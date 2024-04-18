<?php

namespace App\Services;

use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GroupMemberService
{
   public function collection(){
        return GroupMember::all();
   }

   public function store( $id, $inputs){
       
    // dd($inputs,$id);
    // $groupData = json_decode($id, true);  
    // $groupId = $groupData['id'];
    $groupMembers= [];

    foreach($inputs['user_id'] as $userId){
        $existingGroupMember = GroupMember::where('group_id',$id)
                                            ->where('user_id',$userId)
                                            ->first();

        if($existingGroupMember){
            $groupMembers[] = $existingGroupMember;
        }else{
            $newGroupMember = GroupMember::create([
                'group_id'=>$id,
                'user_id'=>$userId,
            ]);
            $groupMembers[] = $newGroupMember;
        }  
    }
    return $groupMembers;       
}

}