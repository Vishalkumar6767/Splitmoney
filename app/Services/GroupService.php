<?php

namespace App\Services;

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupService
{
    public function collection()
    {  
        $userId = Auth::id();
        $groups = Group::where('created_by',$userId)->get();
        return $groups;   
    }

    public function store($inputs)
    {
        // $user = DB::table('users')->where('name', Auth::id())->get();
        $group = Group::create([
            'group_name' => $inputs->group_name,
            'description' => $inputs->description,
            'created_by' => Auth::id(),
             // Automatically set to logged-in user
        ]);
        
        $user = User::select('name', 'email', 'phone_no')->find(Auth::id());
        return[
            'group' => $group,
            'owner' => $user
        ]; // Redirect with success message
    }

    public function resource($id){
       
        // $groups = Group::with(['users' => function ($query) use ($id) {
        //     $query->where('group_id', $id);
        // }])->get();
        $group = Group::whereHas('users', function ($query) use ($id) {
            $query->where('group_id', $id);
           })->with('users')->get();
        return $group;
    }   
   
}
