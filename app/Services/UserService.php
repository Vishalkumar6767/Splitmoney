<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    protected $userObject;
    public function __construct(User $userObject){
        $this->userObject = $userObject;
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
    public function resource($id)
    {
        $user = $this->userObject->with('groups')->findOrFail($id);
        return $user;
    }
    public function update($id, $inputs)
    {
        $data = $this->resource($id);
        $data->update($inputs);
        $success['message'] = "data Updated Successfully";
        return $success;
    }
    public function delete($id)
    {
        $data = $this->resource($id);
        $data->delete();
        $success['message'] = "data Updated Successfully";
        return $success;
    }
}
