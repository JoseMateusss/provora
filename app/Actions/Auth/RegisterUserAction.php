<?php

namespace App\Actions\Auth;

use App\Models\User;

class RegisterUserAction
{
    /**
     * Create a new user with default plan and hashed password.
     *
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function execute(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // User model casts password to hashed
            'plan' => 'free',
            'questions_generated_this_month' => 0,
        ]);
    }
}
