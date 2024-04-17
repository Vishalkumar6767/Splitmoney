<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Token;
use App\Models\InviteGroupMember;
use App\Models\GroupMember;
use Exception;

class AuthService
{
    public function getAllUsers()
    {
        return User::all();
    }
    public function userSignup($inputs)
    {
        $userOtp = UserOtp::whereOtp($inputs['otp'])
            ->where('phone_no', $inputs['phone_no'])
            ->where('type', 'verification')
            ->whereNull('verified_at')
            ->latest()
            ->first();
        if (!$userOtp) {
            $data = [
                'error' => 'OTP is invalid.',
                'code'=>'400'
            ];
            return $data;
        }
        if ($userOtp->otp == $inputs->otp) {
            $user = User::create($inputs->validated());
            $token = $user->createToken(config('app.name'))->accessToken;
            if ($inputs->has('token')) {
                try {
                    $inviteMember = InviteGroupMember::where('token', $inputs->token)
                        ->where('email', $inputs->email)
                        ->first();
                    if ($inviteMember) {
                    $invitedGroupMember = GroupMember::create([
                            'group_id' => $inviteMember->group_id,
                            'user_id' => $user->id,
                        ]);
                        $inviteMember->delete();  
                    }
                } catch (Exception $e) {
                   
                    $data =['error' => 'An error occurred during signup.'];
                    return $data;
                }
                return response()->json([
                    'success' => true,
                    'message' => 'Registration successful!',
                    'token' => $token,
                    'group_detail'=>$invitedGroupMember,
                ]);
            }else{
                return response()->json([
                    'success' => true,
                    'message' => 'Registration successful!',
                    'token' => $token,
                ]);
            }
            // return response()->json([
            //     'success' => true,
            //     'message' => 'Registration successful!',
            //     'token' => $token,
            // ]);
        }
        $data = ['error' => 'Kindly register again'];
        return $data;
    }

    public function sendOtp($inputs)
    {
        $phoneNo = $inputs['phone_no'];
        $otp = mt_rand(100000, 999999);
        UserOtp::create([
            'phone_no' => $phoneNo,
            'otp' => $otp,
            'type' => $inputs['type'],
        ]);
        $data = ['message' => 'Otp send successfully.', 'otp' => $otp];
        return $data;
    }

    public function loginUser($inputs)
    {
        $userOtp = UserOtp::where('phone_no', $inputs['phone_no'])
            ->where('otp', $inputs['otp'])
            ->where('verified_at', null)
            ->where('type', 'login')
            ->latest()
            ->first();
        $user = User::wherePhoneNo($inputs['phone_no'])->first();
        if ($userOtp->otp == $inputs['otp']) {
            $userOtp->update(['verified_at' => now()]);
            return [
                'success' => true,
                'message' => 'You have successfully logged in to your account ',
                'user' => $user,
                'token' => $user->createToken(config('app.name'))->accessToken,
            ];   
        }
         $data = ['error' => 'invalid phone no'];
          return $data;
    }

    public function logout()    
    {
        $user = Auth::user();
        $user->tokens()->delete();
        $data = ['message' => 'Successfully logged out'];
        return $data;
    }
}


