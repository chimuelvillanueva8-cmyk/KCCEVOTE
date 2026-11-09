<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('login', 'Auth::login');
// Local debug session status (development only, localhost only)
$routes->get('_debug/session', 'DevDebug::session');
$routes->post('login/attempt', 'Auth::attemptLogin');
$routes->post('login/face-start', 'Auth::startFaceLogin');
$routes->get('login/face-reference', 'Auth::faceReference');
$routes->post('login/face-reference', 'Auth::saveFaceReference');

// Department & Course management
$routes->get('admin/departments', 'Admin\Departments::index');
$routes->get('admin/departments/list', 'Admin\Departments::list');
$routes->get('admin/departments/all', 'Admin\Departments::all');
$routes->post('admin/departments/store', 'Admin\Departments::store');
$routes->get('admin/departments/(:num)', 'Admin\Departments::edit/$1');
$routes->post('admin/departments/update/(:num)', 'Admin\Departments::update/$1');
$routes->post('admin/departments/delete/(:num)', 'Admin\Departments::delete/$1');
$routes->post('admin/departments/toggle/(:num)', 'Admin\Departments::toggle/$1');

$routes->get('admin/courses', 'Admin\Courses::index');
$routes->get('admin/courses/all', 'Admin\Courses::all');
$routes->post('admin/courses/store', 'Admin\Courses::store');
$routes->get('admin/courses/(:num)', 'Admin\Courses::edit/$1');
$routes->post('admin/courses/update/(:num)', 'Admin\Courses::update/$1');
$routes->post('admin/courses/delete/(:num)', 'Admin\Courses::delete/$1');
$routes->post('admin/courses/toggle/(:num)', 'Admin\Courses::toggle/$1');
$routes->get('courses/by-department/(:num)', 'Admin\Courses::byDepartment/$1');
$routes->post('login/face-verify', 'Auth::faceVerify');
$routes->get('register', 'Auth::register');
$routes->post('register/store', 'Auth::store');
$routes->get('register-new', 'Auth::registerNew');
$routes->post('register/submit', 'Auth::registerSubmit');
$routes->get('logout', 'Auth::logout');
$routes->post('api/check-student-id', 'Auth::checkStudentId');
$routes->post('auth/check-face-registered', 'Auth::checkFaceRegistered');
$routes->get('api/public-stats', 'PublicStatsController::getStats');

$routes->get('admin', 'DashboardController::index');
// Combined management page for departments + courses
$routes->get('admin/manage', 'Admin\Manage::index');
$routes->get('admin/manage/departments', 'Admin\Manage::departments');
$routes->get('admin/manage/courses', 'Admin\Manage::courses');
$routes->get('admin/candidates', 'Admin\Admin::candidates', ['filter' => 'builtInAdmin']);
$routes->get('admin/candidates/create', 'Admin\Admin::createCandidate', ['filter' => 'builtInAdmin']);
$routes->post('admin/candidates/store', 'Admin\Admin::storeCandidate', ['filter' => 'builtInAdmin']);
$routes->get('admin/candidates/edit/(:num)', 'Admin\Admin::editCandidate/$1', ['filter' => 'builtInAdmin']);
$routes->post('admin/candidates/update/(:num)', 'Admin\Admin::updateCandidate/$1', ['filter' => 'builtInAdmin']);
$routes->post('admin/candidates/delete/(:num)', 'Admin\Admin::deleteCandidate/$1', ['filter' => 'builtInAdmin']);
$routes->get('admin/elections', 'Admin\Admin::elections', ['filter' => 'builtInAdmin']);
$routes->get('admin/elections/create', 'Admin\Admin::createElection', ['filter' => 'builtInAdmin']);
$routes->post('admin/elections/store', 'Admin\Admin::storeElection', ['filter' => 'builtInAdmin']);
$routes->get('admin/elections/edit/(:num)', 'Admin\Admin::editElection/$1', ['filter' => 'builtInAdmin']);
$routes->post('admin/elections/update/(:num)', 'Admin\Admin::updateElection/$1', ['filter' => 'builtInAdmin']);
$routes->post('admin/elections/status/(:num)', 'Admin\Admin::updateElectionStatus/$1', ['filter' => 'builtInAdmin']);
$routes->post('admin/elections/delete/(:num)', 'Admin\Admin::deleteElection/$1', ['filter' => 'builtInAdmin']);
$routes->get('admin/announcements', 'Admin\Admin::announcements');
$routes->get('admin/announcements/edit/(:num)', 'Admin\Admin::editAnnouncement/$1');
$routes->post('admin/announcements/store', 'Admin\Admin::storeAnnouncement');
$routes->post('admin/announcements/update/(:num)', 'Admin\Admin::updateAnnouncement/$1');
$routes->post('admin/announcements/delete/(:num)', 'Admin\Admin::deleteAnnouncement/$1');
$routes->get('api/stats', 'ApiController::getStats');
$routes->post('api/azure-face/detect', 'AzureFaceController::detect');
$routes->post('api/azure-face/verify', 'AzureFaceController::verify');

// Student admin routes removed per request (admin UI only).
// Student Management module (admin UI)
$routes->get('admin/students', 'students\Student::index');
$routes->get('admin/students/list', 'students\Student::list');
$routes->post('admin/students/store', 'students\Student::store');
$routes->post('admin/students/update/(:num)', 'students\Student::update/$1');
$routes->post('admin/students/delete/(:num)', 'students\Student::delete/$1');
$routes->post('admin/students/bulk', 'students\Student::bulkAction');
$routes->post('admin/students/import', 'students\Student::import');
$routes->post('admin/students/upload-face/(:num)', 'students\Student::uploadFace/$1');
$routes->post('admin/students/reset-password/(:num)', 'students\Student::resetPassword/$1');

$routes->get('admin/attendance', 'Attendance::index');
$routes->post('admin/attendance/mark', 'Attendance::mark');
$routes->get('admin/attendance/print', 'Attendance::printReport');

$routes->get('admin/positions', 'Admin\Admin::positions', ['filter' => 'builtInAdmin']);
$routes->get('admin/positions/create', 'Admin\Admin::createPosition', ['filter' => 'builtInAdmin']);
$routes->post('admin/positions/store', 'Admin\Admin::storePosition', ['filter' => 'builtInAdmin']);
$routes->get('admin/positions/edit/(:num)', 'Admin\Admin::editPosition/$1', ['filter' => 'builtInAdmin']);
$routes->post('admin/positions/update/(:num)', 'Admin\Admin::updatePosition/$1', ['filter' => 'builtInAdmin']);
$routes->post('admin/positions/delete/(:num)', 'Admin\Admin::deletePosition/$1', ['filter' => 'builtInAdmin']);
$routes->get('admin/parties', 'Admin\Admin::parties', ['filter' => 'builtInAdmin']);
$routes->get('admin/parties/create', 'Admin\Admin::createParty', ['filter' => 'builtInAdmin']);
$routes->post('admin/parties/store', 'Admin\Admin::storeParty', ['filter' => 'builtInAdmin']);
$routes->get('admin/parties/edit/(:num)', 'Admin\Admin::editParty/$1', ['filter' => 'builtInAdmin']);
$routes->post('admin/parties/update/(:num)', 'Admin\Admin::updateParty/$1', ['filter' => 'builtInAdmin']);
$routes->post('admin/parties/delete/(:num)', 'Admin\Admin::deleteParty/$1', ['filter' => 'builtInAdmin']);
$routes->get('admin/admins', 'Admin\Admin::admins');
$routes->post('admin/create-admin', 'Admin\Admin::createAdmin', ['filter' => 'builtInAdmin']);
$routes->post('admin/admins/update/(:num)', 'Admin\Admin::updateAdmin/$1', ['filter' => 'builtInAdmin']);
$routes->post('admin/admins/delete/(:num)', 'Admin\Admin::deleteAdmin/$1', ['filter' => 'builtInAdmin']);
$routes->get('admin/security', 'Admin\Admin::security', ['filter' => 'builtInAdmin']);
$routes->post('admin/security/update', 'Admin\Admin::updateSecurity', ['filter' => 'builtInAdmin']);
$routes->get('admin/results', 'Admin\Admin::results');
$routes->post('admin/results/tie-winner', 'Admin\Admin::saveTieWinner');
$routes->get('admin/audit-logs', 'AuditLogs::index', ['filter' => 'builtInAdmin']);

$routes->get('voting', 'Voting::index');
$routes->get('voting/(:num)', 'Voting::show/$1');
$routes->post('voting/cast', 'Voting::cast');
$routes->post('voting/deactivate-after-vote', 'Voting::deactivateAfterVote');
$routes->post('voting/verify-location', 'Voting::verifyLocation');
$routes->get('student/account-settings', 'Voting::accountSettings');
$routes->post('student/update-settings', 'Voting::updateSettings');
