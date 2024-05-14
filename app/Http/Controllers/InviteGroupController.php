<?php

namespace App\Http\Controllers;

use App\Http\Requests\Group\InviteGroupRequest;
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
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->inviteGroupMemberService->storeMember($request);
        if(isset($data['errors'])){
            return response()->json($data['errors'],400);
        }
        return response()->json($data,200);
       
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
