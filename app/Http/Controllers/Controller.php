<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Todo List API",
 *     description="API for managing tasks and subtasks.",
 *     @OA\Contact(
 *         email="lysak.olexandr@gmail.com"
 *     )
 * )
 * @OA\Server(
 *     url="/api",
 *     description="API server"
 * )
 */
abstract class Controller
{
    use AuthorizesRequests;
}
