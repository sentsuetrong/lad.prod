<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Validation\ValidationRules;

class AuthController extends ResourceController
{
    public function jwtLogin(): ResponseInterface
    {
        $rules = new ValidationRules();
        $rules = $rules->getLoginRules();

        $json = ['message' => 'hello world', 'request' => $this->request->getIPAddress()];
        $json = request()->getJSON(true);

        return $this->respond($json);
    }

    public function register(): ResponseInterface
    {
        $users = auth()->getProvider();

        /* if ($users->findById(request()->getJsonVar('id')))
            return $this->failResourceExists('User already exists.'); */

        $user = new User([
            'username' => request()->getJsonVar('username'),
            'email' => request()->getJsonVar('email'),
            'password' => request()->getJsonVar('password'),
        ]);
        $users->save($user);

        $users->findById($users->getInsertID())->addGroup('officer');

        return $this->respondCreated(['message' => 'User registration successfully']);
    }
}
