<?php

namespace App\Services;

use App\Models\Image;
use App\Models\User;

class UserService
{
    private $userObject;
    public function __construct()
    {
        $this->userObject = new User;
    }
    public function collection($inputs)
    {
        $user = $this->userObject;
        if (isset($inputs['search'])) {
            $searchQuery = $inputs['search'];
            $user = $user->where(function ($query) use($searchQuery) {
                $query->where('name', 'LIKE','%'.$searchQuery.'%')
                      ->orWhere('email', 'LIKE', '%'.$searchQuery.'%');
            });
            return $user->get();  
        }
         return $user->all();
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
    public function upload($inputs)
    {
        $img = $inputs['image'];
        $ext = $img->getClientOriginalExtension();
        $imageName = time() . '.' . $ext;
        $img->move(storage_path('app/public/assets'), $imageName);
        $image = Image::create([
            'image' => $imageName,
        ]);
        $data = [
            'status' => true,
            'message' => "Image uploaded Successfully",
            'path' => asset('storage/assets/' . $imageName),
            'data' => $image
        ];
        return $data;
    }
}
