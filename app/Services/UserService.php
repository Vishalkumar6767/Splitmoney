<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    private $userObject;
    public function __construct()
    {
        $this->userObject = new User;
    }
    public function collection()
    {
        $users = $this->userObject->all();
        return $users;
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
        $success['message'] = "data Updated Successfully";
        return $success;
    }
}
