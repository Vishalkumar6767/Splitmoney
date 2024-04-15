<?php

namespace App\Services;

use App\Mail\SendMail;
use App\Models\InviteGroupMember;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
class InviteGroupMemberService
{

    public function getGroupMember()
    {
        return InviteGroupMember::all();
    }
    public function inviteGroupMembers($inputs)
    {

        $token = Str::uuid();
        $invitation = InviteGroupMember::create([
            'user_id' => Auth::id(),
            'group_id' => $inputs->group_id,
            'email' => $inputs->email,
            'token' => $token,
        ]);

        $invitationLink = config('site.frontWebsiteUrl') . '?token=' . $token;
        Mail::to($inputs->email)->send(new SendMail($invitationLink));
        return response()->json(['message' => 'Invitation sent successfully'], 200);
    }
}
