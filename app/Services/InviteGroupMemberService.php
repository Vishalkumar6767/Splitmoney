<?php

namespace App\Services;

use App\Mail\SendMail;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\InviteGroupMember;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class InviteGroupMemberService
{

    public function collection()
    {
        return InviteGroupMember::all();
    }
    public function store($inputs)
    {
        $group = Group::where('type', "none_group_expenses")->find($inputs['group_id']);
        if ($group) {
            $error['errors'] = [
                'message' => "Group type is None group expense",
                'code' => 400
            ];
            return $error;
        }
        $token = Str::uuid();
        InviteGroupMember::create([
            'user_id' => auth()->id(),
            'group_id' => $inputs['group_id'],
            'email' => $inputs['email'],
            'token' => $token,
        ]);
        $invitationLink = config('site.frontWebsiteUrl') . '?token=' . $token;
        Mail::to($inputs->email)->send(new SendMail($invitationLink));
        $data['message'] = "Invitation sent successfully";
        $data['token'] = $token;
        return $data;
    }
    public function storeMember($inputs)
    {
        $invitedMember = InviteGroupMember::where('token', $inputs['token'])->first();
        $user = User::where('email', $invitedMember['email'])->first();
        if (empty($user)) {
            $errors['errors'] = [
                'message' => "User not Found",
                'code' => 400
            ];
            return $errors;
        }
        $existingGroupMember = GroupMember::where('group_id', $invitedMember['group_id'])
            ->where('user_id', $user['id'])
            ->first();
        if ($existingGroupMember) {
            $message['Message'] = "User already exist in Your Group";
            return $message;
        } else {
            GroupMember::create([
                'group_id' => $invitedMember['group_id'],
                'user_id' => $user['id']
            ]);
            $success['message'] = " Welcome .$user->name. in my group";
            return $success;
        }
    }
}
