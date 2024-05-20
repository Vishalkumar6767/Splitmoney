<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupMember;
use Illuminate\Support\Facades\DB;

class GroupService
{
    public function collection($inputs)
    {
        $includes = [];
        if (!empty($inputs['includes'])) {
            $includes = explode(",", $inputs['includes']);
        }
        $groups = Group::with($includes)->whereHas('members', function ($query) {
            $query->where('user_id', auth()->id());
        });

        return $groups->get();
    }

    public function store($inputs)
    {
        DB::beginTransaction();
        $group = Group::create([
            'name' => $inputs['name'],
            'description' => $inputs['description'],
            'created_by' => auth()->id(),
        ]);
        $group->members()->sync([auth()->id()]);
        DB::commit();
        return [
            'group' => $group,
            'owner' => auth()->user()
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
        $group->update($inputs);
        $success['message'] = "Group Updated successfully";
        return $success;
    }

    public function delete($id)
    {
        $group = $this->resource($id);
        $groupMember = GroupMember::where('group_id', $id);
        $groupMember->delete();
        $group->delete();
        $success['message'] = "Group deleted successfully";
        return $success;
    }
}
