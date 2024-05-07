<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Http\Requests\Auth\SignUp;
use App\Http\Requests\Auth\LoginUser;
use App\Http\Requests\Auth\SendOtp;

class AuthController extends Controller
{
    protected $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function signup(SignUp $request)
    {
        $data = $this->authService->signup($request);

        if (isset($data['errors'])) {
            return response()->json($data['errors'], 400);
        }
        return response()->json($data, 200);
    }

    public function sendOtp(SendOtp $request)
    {
        $userOtp = $this->authService->sendOtp($request);
        if (isset($userOtp['errors'])) {
            return response()->json($userOtp['errors'], 400);
        }
        return response()->json($userOtp, 200);
    }

    public function login(LoginUser $request)
    {
        $data = $this->authService->login($request);
        if (isset($data['errors'])) {
            return response()->json($data['errors'], 400);
        }
        return response()->json($data, 200);
    }

    public function logout()
    {
        $user = $this->authService->logout();
        if (isset($user['errors'])) {
            return response()->json($user['errors'], 400);
        }
        return response()->json($user, 200);
    }
}
