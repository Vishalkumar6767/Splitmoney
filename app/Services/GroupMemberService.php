<?php

namespace App\Services;

use App\Models\GroupMember;

class GroupMemberService
{
    public function collection()
    {
        return GroupMember::all();
    }

    public function store($id, $inputs)
    {
        $groupMembers = [];
        foreach ($inputs['user_id'] as $userId) {
            $existingGroupMember = GroupMember::where('group_id', $id)
                ->where('user_id', $userId)
                ->first();
            if ($existingGroupMember) {
                $groupMembers[] = $existingGroupMember;    
            } else {
                $newGroupMember = GroupMember::create([
                    'group_id' => $id,
                    'user_id' => $userId,
                ]);
                $groupMembers[] = $newGroupMember;
            }
        }
       $success['message'] = "Members are added successfully in the group";
        return $success;
    }
}
