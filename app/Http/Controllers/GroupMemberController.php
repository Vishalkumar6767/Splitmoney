<?php

namespace App\Http\Controllers;
use App\Services\GroupMemberService;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Http\Requests\Group\GroupMember as GroupMemberRequest;

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
    public function index(Request $request)
    {
        $groupMembers = $this->groupMember->collection($request->all());
        if(isset($groupMembers['errors'])){
            return response()->json($groupMembers['errors'],400);  
        }
        return response()->json($groupMembers,200);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(GroupMemberRequest $request)
    {
        $GroupMember = $this->groupMember->store($request->validated());
        if(isset($GroupMember['errors'])){
            return response()->json($GroupMember['errors'], 400);  
        }
        return response()->json($GroupMember, 200);
    }

}