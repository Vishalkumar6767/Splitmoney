<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Image;
use App\Models\User;

class UserService
{
    private $userObject;
    private $groupObject;

    public function __construct(User $user)
    {

        $this->groupObject = new Group;
        $this->userObject = new User;
    }

    public function collection($inputs)
    {
        $users = $this->userObject;
        if (isset($inputs['search'])) {
            $searchQuery = $inputs['search'];
            $users = $users->where(function ($query) use ($searchQuery) {
                $query->where('name', 'LIKE', '%' . $searchQuery . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchQuery . '%');
            });
        }
        $users = $users->orderby('id');
        if (empty($inputs['limit'])) {
            return $users->get();
        }
        return $users->paginate($inputs['limit'], ['*'], 'page', $inputs['page']);
    }


    public function store($inputs)
    {
        $this->userObject->create([
            'name' => $inputs['name'],
            'email' => $inputs['email'],
            'phone_no' => $inputs['phone_no'],
        ]);
        $success['message'] = "Data added successfully";
        return $success;
    }

    public function update($id, $inputs)

    {
        $id = auth()->id();
        $user = $this->userObject->findOrFail($id);
        $user->update($inputs);
        $success['message'] = "data Updated Successfully";
        return $success;
    }
    public function delete($id)
    {
        $id = auth()->id();
        $user = $this->userObject->findOrFail($id);
        $user->delete();
        $success['message'] = "data Deleted Successfully";
        return $success;
    }
    public function upload($inputs)
    {
        if ($inputs['type'] === "USER") {
            $id = auth()->id();
            $user = $this->userObject->findOrFail($id);
            $img = $inputs['url'];
            $ext = $img->getClientOriginalExtension();
            $imageName = time() . '.' . $ext;
            $img->move(storage_path('app/public/assets'), $imageName);
            $image = $user->image()->create([
                'url' => $imageName,
            ]);

            $data = [
                'status' => true,
                'message' => "Image uploaded Successfully",
                'path' => asset('storage/assets/' . $imageName),
                'data' => $image
            ];
        }
        if ($inputs['type'] === "GROUP") {
            $groupId = $inputs['group_id'];
            $group = $this->groupObject->findOrFail($groupId);
            $img = $inputs['url'];
            $ext = $img->getClientOriginalExtension();
            $imageName = time() . '.' . $ext;
            $img->move(storage_path('app/public/assets'), $imageName);
            $image = $group->image()->create([
                'url' => $imageName,
            ]);

            $data = [
                'status' => true,
                'message' => "Image uploaded Successfully",
                'path' => asset('storage/assets/' . $imageName),
                'data' => $image
            ];
        }
        return $data;
    }
}
