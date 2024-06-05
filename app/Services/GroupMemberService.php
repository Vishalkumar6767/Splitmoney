<?php

namespace App\Services;

use App\Models\GroupMember;
use App\Models\Group;

class GroupMemberService
{
    private $groupObject;
    private $groupMemberObject;

    public function __construct()
    {
        $this->groupMemberObject = new GroupMember;
        $this->groupObject = new Group; 
    }
    public function collection($inputs)
    {
        $includes = [];
        if (!empty($inputs['includes'])) {
            $includes = explode(",", $inputs['includes']);
            /* We use explode function to convert the string into an array element//
            for getting the group members and  owner details type owner and members in params*/
        }

        $groups = $this->groupObject->with($includes);
        $groups = $groups->get();
        return $groups;
    }

    public function store($inputs)
    {
        $groupMembers = [];
        foreach ($inputs['user_id'] as $userId) {
            $existingGroupMember = $this->groupMemberObject->where('group_id', $inputs['group_id'])
                ->where('user_id', $userId)
                ->first();
            if ($existingGroupMember) {
                $groupMembers[] = $existingGroupMember;
            } else {
                $newGroupMember = $this->groupMemberObject->create([
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
