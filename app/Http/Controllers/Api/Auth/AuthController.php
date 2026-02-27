<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public $authService;

    public function __construct(AuthService $authService) {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request) {
        $data = $request->validated();

        $result = $this->authService->register($data);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $result,
        ], 201);
    }

    public function login(LoginRequest $request) {
        $credentials = $request->only('email', 'password');

        try {
            $result = $this->authService->login($credentials);

            return response()->json([
                'message' => 'Login successful',
                'data' => $result,
            ], 200);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 401);
        }
    }

    public function logout() {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json([], 204);
    }

    public function refresh() {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
            return response()->json([
                'token' => $newToken,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Could not refresh token: ' . $e->getMessage(),
            ], 401);
        }
    }

    public function me() {
        $user = auth('api')->user();
        return response()->json($user);
    }
}
