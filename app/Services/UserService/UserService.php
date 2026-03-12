<?php

namespace App\Services\UserService;

use App\Models\User;
use App\Traits\FileStorageTrait;

class UserService
{
    use FileStorageTrait;

    public function updateProfile(User $user, array $data) {
        if (isset($data['avatar'])) {
            if ($user->avatar) {
                $this->deleteFile($user->avatar);
            }

            $data['avatar'] = $this->storeFile($data['avatar'], 'avatars', 'img');
        }

        $user->update($data);
        return $user->fresh();
    }

    public function deleteAvatar(User $user) {
        if ($user->avatar) {
            $this->deleteFile($user->avatar);
            $user->update(['avatar' => null]);
        }
        return $user->fresh();
    }
}
