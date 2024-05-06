<?php

namespace App\Http\Controllers;

use App\Http\Requests\Group\InviteMembers;
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
        if (isset($data['errors'])) {
            return response()->json($data, 400);
        } else {
            return response()->json($data, 200);
        }
    }

    public function store(InviteMembers $request)
    {
        $data = $this->inviteGroupMembers->store($request);
        if (isset($data['errors'])) {
            return response()->json($data, 400);
        } else {
            return response()->json($data, 200);
        }
    }
}
