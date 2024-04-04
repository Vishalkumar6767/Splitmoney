<?php


namespace App\Services;

use App\Models\Group;


class GroupService
{
    public function getAllGroup()
    {
        return Group::all();
    }
}
