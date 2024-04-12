<?php


namespace App\Services;

use App\Models\Group;
use App\Models\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupService
{
    public function getAllGroup()
    {  
        $userid = Auth::id();
        
        $userGroup = DB::table('groups')
        ->where('created_by',$userid)
        ->get();
        
        return $userGroup;
    }

    public function create($inputs)
    {
        // $user = DB::table('users')->where('name', Auth::id())->get();

        $group = new Group([
            'group_name' => $inputs->group_name,
            'description' => $inputs->description,
            'created_by' => Auth::id(), // Automatically set to logged-in user
        ]);
        $group->save();
        $user = DB::table('users')
            ->select('name', 'email', 'phone_no')
            ->find(Auth::id());
        return response()->json([
            'group' => $group,
            'owner' => $user
        ]); // Redirect with success message
    }
   
}
