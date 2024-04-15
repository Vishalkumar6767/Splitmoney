<?php

namespace App\Http\Controllers;
use App\Services\AuthService;
use App\Http\Requests\Auth\SignUp;
use App\Http\Requests\Auth\LoginUser;
use App\Http\Requests\Auth\SendOtp;
use Illuminate\Http\JsonResponse;

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
        $data = $users->getData();
        if(isset($data->errors)){
            return response()->json($data,400);
        } else {
            return response()->json($data,200);
        }  
    }

    public function sendOtp(SendOtp $request)
    {
        $userOtp = $this->authService->sendOtp($request);
        if(isset($userOtp['errors'])){
            return response()->json($userOtp,400);
        }else{
            return response()->json($userOtp, 200);
        }  
    }

    public function loginUser(LoginUser $request)
    {
        $users = $this->authService->loginUser($request);
        if(isset($users->errors)){
            return response()->json($users,400);
        }else{
            return response()->json($users, 200);
        }  
    }
    
    public function logout()
    {
        $user = $this->authService->logout();
        if(isset($user['errors'])){
            return response()->json($user,400);
        }else{
            return response()->json($user, 200);
        }   
    }
}
