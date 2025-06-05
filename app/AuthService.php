<?php

declare(strict_types=1);

namespace App;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user and return user + token.
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $token = $user->createToken('api')->plainTextToken;
        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Login user and return user + token.
     */
    public function login(array $data): array
    {
        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        $token = $user->createToken('api')->plainTextToken;
        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout user (revoke token).
     */
    public function logout($user): void
    {
        $user->currentAccessToken()->delete();
    }
}
