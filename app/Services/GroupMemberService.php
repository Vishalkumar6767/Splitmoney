<?php

namespace App\Services;

use App\Models\GroupMember;
use App\Models\Group;

class GroupMemberService
{
    public function collection($inputs)
    {
        $includes = [];
        if (!empty($inputs['includes']))
        {
            $includes = explode(",", $inputs['includes']);
            /* We use explode function to convert the string into an array element//
            for getting the group members and  owner details type owner and members in params*/
        }

        $data = Group::with($includes);
        $data = $data->get();
        return $data;
    }

    public function store($inputs)
    {
        $groupMembers = [];
        foreach ($inputs['user_id'] as $userId) {
            $existingGroupMember = GroupMember::where('group_id', $inputs['group_id'])
                ->where('user_id', $userId)
                ->first();
            if ($existingGroupMember) {
                $groupMembers[] = $existingGroupMember;    
            } else {
                $newGroupMember = GroupMember::create([
                    'group_id' => $inputs['group_id'],
                    'user_id' => $userId,
                ]);
                $groupMembers[] = $newGroupMember;
            }
        }
       $success['message'] = "Members are added successfully in the group";
        return $success;
    }
}
