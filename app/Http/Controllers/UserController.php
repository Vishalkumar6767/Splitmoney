<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\ImageRequest;
use App\Http\Requests\User\Upsert;
use App\Models\User;
use App\Services\UserService; // Import the UserService class
use Illuminate\Http\Request;

class UserController extends Controller
{

    protected $userService;
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Call the getAllUsers method of the userService
        $users = $this->userService->collection($request->all());
        if (isset($users['errors'])) {
            return response()->json($users["errors"], 400);
        }
        return response()->json($users, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Upsert $request)
    {
        $data = User::create($request->validated());
        if (isset($data['errors'])) {
            return response()->json($data['errors'], 400);
        }
        return response()->json($data, 200);
    }

    // Implement other controller methods as needed
    public function update($id, Upsert $request)
    {
       
        $data = $this->userService->update($id, $request->validated());
        if (isset($data['errors'])) {
            return response()->json($data['errors'], 400);
        }
        return response()->json($data, 200);
    }

    public function destroy($id)
    {
        // You can implement logic to delete a specific user here `
        $data = $this->userService->delete($id);
        if (isset($data['errors'])) {
            return response()->json($data['errors'], 400);
        }
        $message = 'User deleted successfully';
        return response()->json(['message' => $message], 200);
    }

    public function upload(ImageRequest $request){
        $image = $this->userService->upload($request->validated());
        if(isset($image['errors'])){
            return response()->json($image['errors'],400);
        }
        return response()->json($image,200);

    }
}
