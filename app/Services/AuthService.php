<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function register(array $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            return $user;
    }

    public function login(array $credentials) {
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                throw new RuntimeException("invalid credentials");
            }

            $user = JWTAuth::user();

            return [
                'token' => $token,
                'user' => $user,
            ];

        } catch (JWTException $e) {
            throw new RuntimeException("Error during login: " . $e->getMessage());
        }
    }
}
