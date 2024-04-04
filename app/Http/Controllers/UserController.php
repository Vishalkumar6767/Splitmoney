<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\Upsert;
use App\Models\User;
use App\Services\UserService; // Import the UserService class

class UserController extends Controller
{

    protected $userService; // Declare a property to hold the UserService instance

    // Inject the UserService instance into the constructor
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
        $users = $this->userService->getAllUsers();
        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Upsert $request)
    {


        $user = User::create($request->validated());


        return response()->json($user, 201);
    }

    // Implement other controller methods as needed
    public function update(Upsert $request, $id)
    {

        $user = User::findOrFail($id);


        $validatedData = $request->validated();


        $user->update($validatedData);

        // Return the updated user
        return response()->json($user, 200);
    }

    public function destroy($id)
    {
        // You can implement logic to delete a specific product here `
        $user = User::findOrFail($id);
        $user = $user->delete();
        $message = 'User deleted successfully';
        return response()->json(['messsage' => $message], 200);
    }
}
