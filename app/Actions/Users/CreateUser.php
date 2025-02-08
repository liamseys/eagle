<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Str;

final class CreateUser
{
    public function handle(array $data): User
    {
        if ($data['send_welcome_email'] === true) {
            $data['password'] = bcrypt(Str::random());
        } else {
            $data['password'] = bcrypt($data['password']);
        }

        $user = User::create($data);

        if ($data['send_welcome_email'] === true) {
            $expiresAt = now()->addDay();

            $user->sendWelcomeNotification($expiresAt);
        }

        return $user;
    }
}
