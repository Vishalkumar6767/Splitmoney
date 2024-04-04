<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Services\GroupService;
use Illuminate\Http\Request;
use App\Http\Requests\Group\Upsert;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $groupService;

    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }
    public function index()
    {
        $group = $this->groupService->getAllGroup();
        return response()->json($group);
    }

    /**
     * Show the form for creating a new resource.
     */

    public function store(Upsert $request)
    {
        $group = Group::create($request->validated());

        return response()->json($group, 201);

    }

    /**
     * Display the specified resource.
     */

    public function update(Upsert $request,$id)
    {
        $group = Group::findOrFail($id);


        $validatedData = $request->validated();


        $group->update($validatedData);

        // Return the updated user
        return response()->json($group, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $id)
    {
        $group = Group::findOrFail($id);
        $group = $group->delete();

    }
}
