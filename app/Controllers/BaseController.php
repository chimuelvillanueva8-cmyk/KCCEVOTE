<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Libraries\CampusLocation;
use App\Models\ElectionModel;
use App\Models\SettingModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        (new ElectionModel())->syncScheduledStatuses();

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    protected function getCampusSettings(): array
    {
        $settingModel = new SettingModel();

        return [
            'campus_latitude'      => $settingModel->getValue('campus_latitude', ''),
            'campus_longitude'     => $settingModel->getValue('campus_longitude', ''),
            'campus_access_radius' => $settingModel->getValue('campus_access_radius', ''),
        ];
    }

    protected function calculateDistanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        return CampusLocation::calculateDistanceMeters($lat1, $lng1, $lat2, $lng2);
    }

    protected function isCampusLocationConfigured(): bool
    {
        return CampusLocation::isConfigured($this->getCampusSettings());
    }

    protected function isWithinCampusRange(?float $latitude, ?float $longitude): bool
    {
        return CampusLocation::isWithinRange($latitude, $longitude, $this->getCampusSettings());
    }
}
