<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserProfile\UpdateAvatarRequest;
use App\Http\Resources\User\UserResource;
use App\Services\UserService\UserService;

class ProfileController extends Controller
{
    public $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function show() {
        return new UserResource(auth('api')->user());
    }

    public function update(UpdateAvatarRequest $request) {
        $user = auth('api')->user();
        $updatedUser = $this->userService->updateProfile($user, $request->only('avatar'));

        return new UserResource($updatedUser);
    }

     public function deleteAvatar() {
        $result = $this->userService->deleteAvatar(auth('api')->user());
        return new UserResource($result);
    }
}
