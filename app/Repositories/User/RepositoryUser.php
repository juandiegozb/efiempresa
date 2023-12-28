<?php

namespace App\Repositories\User;

use App\Models\User;

class RepositoryUser {

    public function createUser($data) {
        return User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => bcrypt($data->password)
        ]);
    }

}
