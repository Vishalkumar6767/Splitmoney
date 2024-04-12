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


        $users = $this->authService->userSignup($request);

        // return response()->json($users, 200);
        try {
           
            return response()->json($users, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred']);
        }
    }

    public function sendOtp(SendOtp $request)
    {

        $userotp = $this->authService->sendOtp($request);

        return response()->json($userotp, 200);
    }
    public function loginUser(LoginUser $request)
    {
        $users = $this->authService->loginUser($request);

        return response()->json($users, 201);
    }
    
    public function logout()
    {
        $user = $this->authService->logout();
        return response()->json($user, 201);
    }
}
