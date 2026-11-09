<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->get('role') === 'admin') {
            $adminId = filter_var(session()->get('admin_id'), FILTER_VALIDATE_INT);
            $db = \Config\Database::connect();
            $isBuiltInAdmin = false;

            if ($adminId !== false && $adminId > 0 && $db->fieldExists('is_builtin_admin', 'admins')) {
                $isBuiltInAdmin = $db->table('admins')
                    ->where('id', $adminId)
                    ->where('is_builtin_admin', 1)
                    ->countAllResults() === 1;
            }

            session()->set('is_builtin_admin', $isBuiltInAdmin);

            return null;
        }

        if (
            $request instanceof IncomingRequest
            && ($request->isAJAX() || str_starts_with(trim($request->getUri()->getPath(), '/'), 'api/'))
        ) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON(['error' => 'Unauthorized']);
        }

        return redirect()->to(base_url('login?action=admin'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
