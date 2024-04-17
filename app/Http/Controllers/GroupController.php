<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Http\Controllers\Controller;
use App\Http\Requests\Group\Upsert;
use App\Services\GroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


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
        $group = $this->groupService->create($request);
        // $group = Group::create($request->validated());
        return response()->json($group, 200);
    }

    /**
     * Display the specified resource.
     */
    public function update(Upsert $request, $id)
    {

        $group = Group::findOrFail($id);
        $validatedData = $request->validated();
        $group->update($validatedData);
        return response()->json($group, 200);
    }

    public function destroy($id)
    {
        $group = Group::findOrFail($id);
        $group->delete();
        return response()->json(['data'=>"Group Deleted Successfully"],200);   
    }
}