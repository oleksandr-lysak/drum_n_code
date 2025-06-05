<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use App\TaskStatusEnum;

/**
 * @OA\Schema(
 *   schema="TaskPolicy",
 *   type="object",
 *   @OA\Property(property="viewAny", type="boolean", example=false),
 *   @OA\Property(property="view", type="boolean", example=true),
 *   @OA\Property(property="create", type="boolean", example=false),
 *   @OA\Property(property="update", type="boolean", example=true),
 *   @OA\Property(property="delete", type="boolean", example=true),
 *   @OA\Property(property="restore", type="boolean", example=false),
 *   @OA\Property(property="forceDelete", type="boolean", example=false)
 * )
 */
class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        return $task->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        return $task->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     * Completed tasks cannot be deleted.
     */
    public function delete(User $user, Task $task): bool
    {
        return $task->user_id === $user->id && $task->status !== TaskStatusEnum::DONE;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return false;
    }
}
