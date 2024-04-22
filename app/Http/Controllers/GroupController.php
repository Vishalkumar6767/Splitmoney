<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Group\Upsert;
use App\Services\GroupService;


class GroupController extends Controller
{
    protected $groupService;
    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    public function index()
    {
        $data = $this->groupService->collection();
        if (isset($data['errors'])) {
            return response()->json($data);
        }
        return response()->json($data);
    }

    public function store(Upsert $request)
    {
        $data = $this->groupService->store($request);
        if (isset($data['errors'])) {
            return response()->json($data, 200);
        }
        return response()->json($data, 200);
    }

    public function show(string $id)
    {
        $groupMembers = $this->groupService->resource($id);
        if (isset($groupMembers['errors'])) {
            return response()->json($groupMembers, 400);
        }
        return response()->json($groupMembers, 200);
    }
    public function update($id, Upsert $request)
    {
        $data = $this->groupService->update($id, $request->validated());
        return response()->json($data, 200);
    }

    public function destroy($id)
    {
        $data = $this->groupService->delete($id);
        return response()->json($data, 200);
    }
}
