<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserOtp;

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
                'message' => 'OTP is invalid.',
            ], 400);
        }
        if ($userOtp && $userOtp->otp == $inputs['otp']) {

            // You may want to add validation and other necessary checks before creating the user
            $user = User::create($inputs->validated());

            return response()->json([
                'success' => true,
                'message' => 'Registration successful!',
                'token' => $user->createToken(config('app.name'))->accessToken,
            ]);
        }

        return response()->json([
            'error' => 'Kindly register again',
        ]);
    }

    public function sendOtp($inputs)
    {
        // if (isset($phoneNo) && $phoneNo->exists()) {
        //     return response()->json(['error' => 'phoneNO exist is Exist in a table']);
        // }
        $phoneNo = $inputs['phone_no'];
        $otp = mt_rand(100000, 999999);
        UserOtp::create([
            'phone_no' => $phoneNo,
            'otp' => $otp,
            'type' => $inputs['type'],
        ]);

        $data = ['message' => 'Otp send successfully.', 'otp' => $otp];

        return response()->json($data);
    }
    public function loginUser($inputs)
    {
        // $phoneNo = $inputs->get('phone_no');
        // $user = User::wherePhoneNo($phoneNo)->first();

        // if (!$user) {
        //     return response()->json(['error' => 'Invalid phone number'], 400);
        // }
        $userOtp = UserOtp::where('phone_no', $inputs['phone_no'])
            ->where('otp', $inputs['otp'])
            ->where('verified_at', null)
            ->where('type', 'login')
            ->latest()
            ->first();

        $user = User::wherePhoneNo($inputs['phone_no'])->first();

        // if ($userOtp->type != $inputs['type']) {
        //     return response()->json(['error' => 'type is not matching kindly check it'], 400);
        // }

        if ($userOtp->otp == $inputs['otp']) {

            $userOtp->update(['verified_at' => now()]);
            return response()->json([
                'success' => true,
                'message' => 'You have successfully logged in to your account ',
                'user' => $user,
                'token' => $user->createToken(config('app.name'))->accessToken,
            ]);
        }
        return response()->json(['error' => 'invalid phone no'], 400);
    }
}
