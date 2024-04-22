<?php

namespace App\Http\Controllers;

use App\Http\Requests\Group\InviteMembers;
use Illuminate\Http\Request;
use App\Services\InviteGroupMemberService;

class InviteGroupMemberController extends Controller
{

    protected $inviteGroupMembers;
    public function __construct(InviteGroupMemberService $inviteGroupMembers)
    {
        $this->inviteGroupMembers = $inviteGroupMembers;
    }

    public function index()
    {
        $data = $this->inviteGroupMembers->collection();
        if(isset($data['errors'])){
            return response()->json($data,400);
        }else{
            return response()->json($data,200);
        }
    }

    public function store(InviteMembers $request)
    {
        $data = $this->inviteGroupMembers->store($request);
        if(isset($data['errors'])){
            return response()->json($data, 400);
        }else{
            return response()->json($data, 200);
        }
        
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
