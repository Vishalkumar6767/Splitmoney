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
    private $userObject;
    private $otpObject;

    public function __construct()
    {
        $this->userObject = new User;
        $this->otpObject = new UserOtp;

    }

    public function signup($inputs)
    {
        $userOtp = $this->otpObject->whereOtp($inputs['otp'])
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
            $user = $this->userObject->create($inputs->validated());
            $token = $user->createToken(config('app.name'))->accessToken;
            $group = Group::create([
                'name' => "Non-group expenses",
                'type' => "none_group_expenses",
                'description' => "own group",
                'created_by' => $user->id,
            ]);
            $userOtp->delete();
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
        $this->otpObject->create([
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
        $userOtp = $this->otpObject->where('phone_no', $inputs['phone_no'])
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
        $user = $this->userObject->where('phone_no', $inputs['phone_no'])->first();
        if (!$user) {
            $errors['errors'] = [
                'message' => "User does not exist",
                'code' => 400
            ];
            return $errors;
        }
        $userOtp = $this->otpObject->where('phone_no', $inputs['phone_no'])
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
            $userOtp->delete();
        }
        return $data;
    }

    public function authenticatedUser()
    {

        $id = auth()->id();
        $user = $this->userObject->findOrFail($id);
        $userImage = $user->image;
        $imagePath = $userImage ? asset('storage/assets/'.$userImage->url) : null;
        $user->image_url = $imagePath;
        unset($user->image);
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
