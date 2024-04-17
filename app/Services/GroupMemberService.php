<?php

namespace App\Services;

use App\Models\GroupMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GroupMemberService
{
   public function getGroupMember(){
        return GroupMember::all();
   }

    public function create($inputs)
    {
        // $user = DB::table('users')->where('name', Auth::id())->get();
        $groupMember = new GroupMember([
            'group_id' => $inputs->group_id,
            'user_id' => $inputs->user_id,
             // Automatically set to logged-in user
        ]);
        $groupMember->save();
        $user = DB::table('users')
        ->select('id')
        ->find(Auth::id());
        return response()->json([
            'group_id' => $groupMember,
            'user_id' => $user
        ]); // Redirect with success message
    }
}