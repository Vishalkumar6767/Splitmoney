<?php

namespace App\Services;

use App\Models\Group;

class GroupService
{
    public function collection($inputs)
    {
        $includes = [];
        if (!empty($inputs['includes']))
        {
            $includes = explode(",", $inputs['includes']);
        }
        $data = Group::with($includes);
        $data = $data->where('created_by',auth()->id())->get();
        return $data;
    }

    public function store($inputs)
    {
        $data = Group::create([
            'name' => $inputs['name'],
            'description' => $inputs['description'],
            'created_by' => auth()->id(),
            // Automatically set to logged-in user
        ]);
        return [
            'group' => $data,
            'owner' => auth()->user()
        ];
    }

    public function resource($id)
    {

        $data = Group::with('members')->findOrFail($id);
        return $data;
    }
    public function update($id, $inputs)
    {
        $data = $this->resource($id);
        $data->update($inputs);
        $success['message'] = "Group Updated successfully";
        return $success;
    }

    public function delete($id)
    {
        $data = $this->resource($id);
        $data->delete();
        $success['message'] = "Group deleted successfully";
        return $success;
    }
}
