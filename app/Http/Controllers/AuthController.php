<?php

namespace App\Http\Controllers;


use App\Services\AuthService;
use App\Http\Requests\Auth\Login;
use App\Http\Requests\Auth\SignUp;
use App\Http\Requests\Auth\LoginUser;
use App\Http\Requests\Auth\SendOtp;





class AuthController extends Controller
{
    //


    protected $authService;


    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }


    // public function signupp(SignUp $request)
    // {
    //     $users = $this->authService->authSignup($request);

    //     return response()->json($users, 201);
    // }
    public function signup(SignUp $request)
    {


        $users = $this->authService->userSignup($request);

        return response()->json($users, 201);
    }


    // public function verifyOtp(Login $request)
    // {

    //     $userotp = $this->authService->verifyOtp($request);

    //     return response()->json($userotp, 201);
    // }

    public function sendOtp(SendOtp $request)
    {

        $userotp = $this->authService->sendOtp($request);

        return response()->json($userotp, 201);
    }
    public function loginUser(LoginUser $request)
    {
        $users = $this->authService->loginUser($request);

        return response()->json($users, 201);
    }
}



