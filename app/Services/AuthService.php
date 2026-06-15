<?php

namespace App\Services;

use App\Models\User;
use App\Interfaces\AuthInterface;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthInterface
{
    public function createUser(array $data): User
    {
        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    public function findUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function updatePassword(User $user, string $password): bool
    {
        return $user->update([
            'password' => Hash::make($password)
        ]);
    }

    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    public function createToken(User $user, string $tokenName = 'auth_token'): string
    {
        return $user->createToken($tokenName)->plainTextToken;
    }

}
