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
            return response()->json([
                'error' => 'OTP is invalid.',
                'code'=>'400'
            ]);
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
                        GroupMember::create([
                            'group_id' => $inviteMember->group_id,
                            'user_id' => $user->id,
                        ]);
                        $inviteMember->delete();
                    }
                } catch (Exception $e) {
                    // Log or handle database errors here
                    return response()->json([
                        'error' => 'An error occurred during signup.',
                    ], 500);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Registration successful!',
                'token' => $token,
            ]);
        }

        return response()->json([
            'error' => 'Kindly register again',
        ]);
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
            $response = [
                'success' => true,
                'message' => 'You have successfully logged in to your account ',
                'user' => $user,
                'token' => $user->createToken(config('app.name'))->accessToken,
            ];
            unset($response['headers']);
            unset($response['original']);
            return response()->json($response);
        }
        return response()->json(['error' => 'invalid phone no'], 400);
    }

    public function logout()
    {
        // Revoke the access token associated with the authenticated user
        $user = Auth::user();
        $user->tokens()->delete();
        return response()->json(['message' => 'Successfully logged out']);
    }
}
