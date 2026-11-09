<?php

namespace App\Controllers;

class DevDebug extends BaseController
{
    public function session()
    {
        if (ENVIRONMENT === 'production') {
            return $this->response->setStatusCode(404);
        }

        // Restrict to localhost for safety
        $remote = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!in_array($remote, ['127.0.0.1', '::1', 'localhost'])) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }

        $sess = session();
        $data = [
            'role' => $sess->get('role'),
            'isLoggedIn' => (bool) $sess->get('isLoggedIn'),
        ];

        return $this->response->setJSON($data);
    }
}
