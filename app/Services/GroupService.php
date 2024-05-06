<?php

namespace App\Services;

use App\Models\Group;
use App\Models\User;

class GroupService
{
    public function collection($inputs)
    {
        $userId =  auth()->id();
        $groups = Group::where('created_by', $userId)->get();
        return $groups;
    }

    public function store($inputs)
    {
        $group = Group::create([
            'name' => $inputs['name'],
            'description' => $inputs['description'],
            'created_by' => auth()->id(),
            // Automatically set to logged-in user
        ]);
        $user = User::select('name', 'email', 'phone_no')->findOrFail(auth()->id());
        return [
            'group' => $group,
            'owner' => $user
        ];
    }

    public function resource($id)
    {

        $group = Group::with('members')->findOrFail($id);
        return $group;
    }
    public function update($id, $inputs)
    {
        $group = $this->resource($id);
        if (empty($group)) {
            $error['message'] = "Group not found";
            $error['code'] = 400;
            return $error;
        }
        $group->update($inputs);
        return $group;
    }

    public function delete($id)
    {
        $group = $this->resource($id);
        if (isset($group['error'])) {
            return $group;
        }
        $group->delete();
        $data['message'] = "Group deleted successfully";
        return $data;
    }
}
