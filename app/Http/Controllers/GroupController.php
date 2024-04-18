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

    public function store(Upsert $request)
    {
        $group = $this->groupService->create($request);
        
        return response()->json($group, 200);
    }

    public function show(string $id)
    {
         
        $groupMembers = $this->groupService->getGroupMembers($id);
        if(isset($groupMembers['errors'])){
            return response()->json($groupMembers,400);
        }else{
            return response()->json($groupMembers, 200);
        }  
    }
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