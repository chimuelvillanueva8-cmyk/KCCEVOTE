<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class BuiltInAdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $adminId = filter_var(session()->get('admin_id'), FILTER_VALIDATE_INT);
        $db = \Config\Database::connect();
        $isBuiltInAdmin = $adminId !== false
            && $adminId > 0
            && $db->fieldExists('is_builtin_admin', 'admins')
            && $db->table('admins')
                ->where('id', $adminId)
                ->where('is_builtin_admin', 1)
                ->countAllResults() === 1;

        if ($isBuiltInAdmin) {
            return null;
        }

        if ($request instanceof IncomingRequest && $request->isAJAX()) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON([
                    'success' => false,
                    'message' => 'Only the built-in administrator can access this section.',
                ]);
        }

        return redirect()->to(base_url('admin'))
            ->with('error', 'Only the built-in administrator can access this section.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
