<?php

namespace App\Http\Controllers;
use App\Services\GroupMemberService;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Http\Requests\Group\GroupMember;

class GroupMemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    protected $groupMember;

    public function __construct(GroupMemberService $groupMemberService)
    {
        $this->groupMember = $groupMemberService;
    }
    public function index()
    {
        $groupMembers = $this->groupMember->collection();
        if(isset($groupMembers['errors'])){
            return response()->json($groupMembers,400);  
        }
        return response()->json($groupMembers,200);
    }

  
    /**
     * Store a newly created resource in storage.
     */
    public function store(Group $group, GroupMember $request)
    {
        $GroupMember = $this->groupMember->store($group->id,$request);
        if(isset($GroupMember['errors'])){
            return response()->json($GroupMember, 400);  
        }
        return response()->json($GroupMember, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}