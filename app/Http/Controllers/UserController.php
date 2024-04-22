<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\Upsert;
use App\Models\User;
use App\Services\UserService; // Import the UserService class

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
    public function index()
    {
        // Call the getAllUsers method of the userService
        $users = $this->userService->collection();
        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Upsert $request)
    {
        $data = User::create($request->validated());
        if(isset($data['errors'])){
            return response()->json($data, 400);
        }
        return response()->json($data, 200);
              
    }

    // Implement other controller methods as needed
    public function update(Upsert $request, $id)
    {
        $user = User::findOrFail($id);
        $validatedData = $request->validated();
        $user->update($validatedData);
        return response()->json($user, 200);
    }

    public function destroy($id)
    {
        // You can implement logic to delete a specific user here `
        $user = User::findOrFail($id);
        $user = $user->delete();
        $message = 'User deleted successfully';
        return response()->json(['message' => $message], 200);
    }
}
