<?php

namespace App\Http\Controllers;

use App\Services\InviteGroupMemberService;
use Illuminate\Http\Request;

class InviteGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $inviteGroupMemberService;
    public function __construct(InviteGroupMemberService $inviteGroupMemberService){
        $this->inviteGroupMemberService = $inviteGroupMemberService;
    }
  
    public function store(Request $request)
    {
        $data = $this->inviteGroupMemberService->storeMember($request);
        if(isset($data['errors'])){
            return response()->json($data['errors'],400);
        }
        return response()->json($data,200);
       
    }
}
