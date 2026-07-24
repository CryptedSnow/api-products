<?php

namespace App\Repositories;

use App\Models\User;

interface AuthRepository
{
    public function createUser(array $data): User;
    public function findUserByEmail(string $email): ?User;
    public function updatePassword(User $user, string $password): bool;
    public function revokeAllTokens(User $user): void;
    public function createToken(User $user, string $tokenName = 'auth_token'): string;
}
