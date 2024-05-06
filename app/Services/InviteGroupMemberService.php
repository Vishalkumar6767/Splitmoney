<?php

namespace App\Services;

use App\Mail\SendMail;
use App\Models\InviteGroupMember;
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
}
