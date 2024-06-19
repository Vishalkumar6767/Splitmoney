<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseParticipation;
use App\Models\Group;
use App\Models\GroupMember;
use Illuminate\Support\Facades\DB;

class GroupService
{
    private $groupObject;
    private $groupMemberObject;
    private $userParticipatedInExpense;

    public function __construct()
    {
        $this->groupMemberObject = new GroupMember;
        $this->groupObject = new Group;
        $this->userParticipatedInExpense = new ExpenseParticipation;
    }
  

public function collection($inputs)
{
    $includes = [];
    if (!empty($inputs['includes'])) {
        $includes = explode(",", $inputs['includes']);
    }

    // Ensure includes array has necessary relationships
    if (!in_array('members', $includes)) {
        $includes[] = 'members';
    }
    if (!in_array('expenses', $includes)) {
        $includes[] = 'expenses';
    }

    // Fetch groups with the necessary relationships
    $groups = $this->groupObject->with($includes)
        ->whereHas('members', function ($query) {
            $query->where('user_id', auth()->id());
        });

    $groupDetails = $groups->get();
    $overallNetAmount = 0;

    foreach ($groupDetails as $group) {
        $totalPaidByUser = $group->expenses()
            ->where('payer_user_id', auth()->id())
            ->where('type', '!=', 'SETTLEMENT')
            ->sum('amount');
        $totalBorrowedByUser = $this->userParticipatedInExpense->where('user_id', auth()->id())
            ->whereIn('expense_id', $group->expenses->pluck('id'))
            ->sum('owned_amount');
        $totalSettledAmount = $group->expenses()
            ->where('payer_user_id', auth()->id())
            ->where('type', 'SETTLEMENT')
            ->sum('amount');
        $netAmount = $totalPaidByUser - $totalBorrowedByUser;
        $netAmountAfterSettle = abs($netAmount) - $totalSettledAmount;

        if ($netAmount == 0 && $totalBorrowedByUser == 0) {
            $type = "No Expenses";
            $amount = 0;
        } else {
            $type = ($netAmountAfterSettle > 0) ? "lent" : (($netAmountAfterSettle < 0) ? "borrowed" : "Balanced");
            $amount = abs($netAmount);
        }

        $group->groupStatistics = [
            'amount' => $amount,
            'type' => $type,
        ];
        $groupImage = $group->image;
        $imagePath = $groupImage ? asset('storage/assets/' . $groupImage->url) : null;
        $group->image_url = $imagePath;
        unset($group->image);
        $overallNetAmount += $netAmountAfterSettle;
    }

    $overallType = ($overallNetAmount > 0) ? "lent" : (($overallNetAmount < 0) ? "borrowed" : "Balanced");

    return [
        'groups' => $groupDetails,
        'overall' => abs($overallNetAmount),
        'overall_type' => $overallType
    ];
}



    public function store($inputs)
    {
        DB::beginTransaction();
        $group = $this->groupObject->create([
            'name' => $inputs['name'],
            'type' => $inputs['type'],
            'description' => $inputs['description'],
            'created_by' => auth()->id(),
        ]);
        $group->members()->sync([auth()->id()]);

        DB::commit();
        return [
            'group' => $group,
            'owner' => auth()->user(),
        ];
    }
    public function resource($id)
    {
        $group = $this->groupObject->with('members.image')->findOrFail($id);
        $groupImage = $group->image;
        $imagePath = $groupImage ? asset('storage/assets/' . $groupImage->url) : null;
        $group->image_url = $imagePath;
        unset($group->image);
        foreach ($group->members as $member) {
            $memberImage = $member->image;
            $imagePath = $memberImage ? asset('storage/assets/' . $memberImage->url) : null;
            $member->image_url = $imagePath;
            unset($member->image);
        }

        return $group;
    }


    public function update($id, $inputs)
    {
        $group = $this->groupObject->findOrFail($id);
        $group->update($inputs);
        $success['message'] = "Group Updated successfully";
        return $success;
    }

    public function delete($id)
    {
        $group = $this->groupObject->findOrFail($id);
        if ($group->type == "none_group_expenses") {
            return [
                'errors' => [
                    'message' => "This is None expense group",
                    'code' => 400
                ]
            ];
        }
        DB::beginTransaction();
        try {
            $expenses = Expense::where('group_id', $id)->get();
            foreach ($expenses as $expense) {
                $expense->settlements()->delete();
            }
            Expense::where('group_id', $id)->delete();
            $this->groupMemberObject->where('group_id', $id)->delete();
            $group->delete();
            DB::commit();
            return [
                'message' => "Group deleted successfully"
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'errors' => [
                    'message' => $e->getMessage(),
                    'code' => 500
                ]
            ];
        }
    }
}
