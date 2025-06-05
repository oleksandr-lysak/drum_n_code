<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\AuthService;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Authentication endpoints"
 * )
 */
class AuthController extends Controller
{
    public function __construct(protected AuthService $service)
    {
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name","email","password","password_confirmation"},
     *         @OA\Property(property="name", type="string", example="User Name"),
     *         @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *         @OA\Property(property="password", type="string", format="password", example="password"),
     *         @OA\Property(property="password_confirmation", type="string", format="password", example="password")
     *     )),
     *     @OA\Response(response=201, description="User registered", @OA\JsonContent(
     *         @OA\Property(property="user", type="object", @OA\Property(property="id", type="integer")),
     *         @OA\Property(property="token", type="string")
     *     ))
     * )
     */
    public function register(RegisterRequest $request)
    {
        $result = $this->service->register($request->validated());
        return response()->json($result, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login user and get token",
     *     tags={"Auth"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"email","password"},
     *         @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *         @OA\Property(property="password", type="string", format="password", example="password")
     *     )),
     *     @OA\Response(response=200, description="User logged in", @OA\JsonContent(
     *         @OA\Property(property="user", type="object", @OA\Property(property="id", type="integer")),
     *         @OA\Property(property="token", type="string")
     *     )),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function login(LoginRequest $request)
    {
        $result = $this->service->login($request->validated());
        return response()->json($result);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout user (revoke token)",
     *     tags={"Auth"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Logged out", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Logged out")
     *     ))
     * )
     */
    public function logout(Request $request)
    {
        $this->service->logout($request->user());
        return response()->json(['message' => 'Logged out']);
    }
}
