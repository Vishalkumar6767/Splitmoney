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
        $inviteGroupMembers = $this->inviteGroupMembers->getGroupMember();
        return response()->json($inviteGroupMembers);
    }

    /**
     * Show the form for creating a new resource.
     */

    public function store(InviteMembers $request)
    {
        $inviteGroupMembers = $this->inviteGroupMembers->inviteGroupMembers($request);
        return response()->json($inviteGroupMembers, 200);
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
