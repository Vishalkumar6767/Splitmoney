<?php

namespace App\Services;

use App\Models\Group;
use App\Models\User;
use App\Models\UserOtp;
use App\Models\InviteGroupMember;
use App\Models\GroupMember;
use Exception;
use Illuminate\Support\Facades\DB;

class AuthService
{
    public function getAllUsers()
    {
        return User::all();
    }
    public function signup($inputs)
    {
        $userOtp = UserOtp::whereOtp($inputs['otp'])
            ->where('phone_no', $inputs['phone_no'])
            ->where('type', 'verification')
            ->whereNull('verified_at')
            ->latest()
            ->first();
        if (!$userOtp) {
            $error['errors'] = [
                'message' => "Otp is invalid",
                'code' => 400
            ];
            return $error;
        }
        $data = [];
        if ($userOtp->otp == $inputs->otp) {
            DB::beginTransaction();
            $user = User::create($inputs->validated());
            $token = $user->createToken(config('app.name'))->accessToken;
            $group = Group::create([
                'name' => "none-group expense",
                'type' => "none_group_expenses",
                'description' => "own group",
                'created_by' => $user->id,
            ]);
            $group->members()->sync([$user->id]);
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
                }
                $data = [
                    'success' => true,
                    'message' => 'Registration successful!',
                    'token' => $token,
                    'group_detail' => $invitedGroupMember,
                ];
            } else {
                $data = [
                    'success' => true,
                    'message' => 'Registration successful!',
                    'token' => $token,
                ];
            }

            DB::commit();
            return $data;
        }
    }

    public function sendOtp($inputs)
    {
        $otp = mt_rand(100000, 999999);
        UserOtp::create([
            'phone_no' => $inputs['phone_no'],
            'otp' => $otp,
            'type' => $inputs['type'],
        ]);
        $data['message'] = "Otp send successfully.";
        $data['otp'] = $otp;
        return $data;
    }
    public function resendOtp($inputs)
    {
        $otp = mt_rand(100000, 999999);
        $userOtp = UserOtp::where('phone_no', $inputs['phone_no'])
            ->where('type', $inputs['type'])->first();
        if (empty($userOtp)) {
            $errors['errors'] = [
                'message' => "Not a Valid Number",
                'code' => 400
            ];
            return $errors;
        }
        UserOtp::create([
            'phone_no' => $userOtp['phone_no'],
            'otp' => $otp,
            'type' => $userOtp['type'],
        ]);
        $data['message'] = "Otp Resend successfully.";
        $data['otp'] = $otp;
        return $data;
    }

    public function login($inputs)
    {
        $user = User::where('phone_no', $inputs['phone_no'])->first();
        if (!$user) {
            $errors['errors'] = [
                'message' => "Invalid User",
                'code' => 400
            ];
            return $errors;
        }
        $userOtp = UserOtp::where('phone_no', $inputs['phone_no'])
            ->where('otp', $inputs['otp'])
            ->whereNull('verified_at')
            ->where('type', 'login')
            ->latest()
            ->first();
        if (!$userOtp) {
            $errors['errors'] = [
                'message' => "Invalid Otp",
                'code' => 400
            ];
            return $errors;
        }
        $data = [];
        if ($userOtp->otp == $inputs['otp']) {
            $userOtp->update(['verified_at' => now()]);
            $data = [
                'success' => true,
                'message' => 'You have successfully logged in to your account ',
                'user' => $user,
                'token' => $user->createToken(config('app.name'))->accessToken,
            ];
        }
        return $data;
    }
    public function authenticatedUser()
    {
        $id = auth()->id();
        $user = User::findOrFail($id);
        return $user;
    }
    public function logout()
    {
        $user = auth()->user();
        $user->tokens()->delete();
        $data['message'] = 'Successfully logged out';
        return $data;
    }
}
