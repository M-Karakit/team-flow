<?php

namespace App\Http\Controllers\Api\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\AssignMemberRequest;
use App\Http\Requests\Member\UpdateRoleRequest;
use App\Http\Resources\User\UserResource;
use App\Models\Project\Project;
use App\Models\User;
use App\Services\Project\MemberService;

class MemberController extends Controller
{
    public $memberService;

    public function __construct(MemberService $memberService) {
        $this->memberService = $memberService;
    }

    public function index(Project $project) {
        return UserResource::collection($project->members()->paginate(10));
    }

    public function assignMember(AssignMemberRequest $request, Project $project) {
        $this->memberService->assignMember($project, $request->user_id, $request->role);
        return response()->json(['message' => 'Member assigned successfully.']);
    }

    public function updateRole(UpdateRoleRequest $request, Project $project, User $user) {
        $this->memberService->updateRole($project, $user, $request->role);
        return response()->json(['message' => 'Member role updated successfully.']);
    }

    public function removeMember(Project $project, User $user) {
        $this->memberService->removeMember($project, $user);
        return response()->json(['message' => 'Member removed successfully.']);
    }
}
