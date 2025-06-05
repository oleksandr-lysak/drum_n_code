<?php

declare(strict_types=1);

namespace App\DTO;

use App\TaskStatusEnum;
use App\TaskPriorityEnum;

/**
 * @OA\Schema(
 *   schema="Task",
 *   type="object",
 *   required={"id","user_id","status","priority","title","created_at"},
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="user_id", type="integer", example=1),
 *   @OA\Property(property="parent_id", type="integer", nullable=true, example=null),
 *   @OA\Property(property="status", type="string", enum={"todo","done"}, example="todo"),
 *   @OA\Property(property="priority", type="integer", minimum=1, maximum=5, example=3),
 *   @OA\Property(property="title", type="string", example="Main Task"),
 *   @OA\Property(property="description", type="string", nullable=true, example="Description"),
 *   @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-04T12:00:00Z"),
 *   @OA\Property(property="completed_at", type="string", format="date-time", nullable=true, example=null),
 *   @OA\Property(property="children", type="array", @OA\Items(ref="#/components/schemas/Task"))
 * )
 */
class TaskDTO
{
    public array $children = [];

    public function __construct(
        public readonly ?int $id,
        public readonly int $user_id,
        public readonly ?int $parent_id,
        public readonly TaskStatusEnum $status,
        public readonly TaskPriorityEnum $priority,
        public readonly string $title,
        public readonly ?string $description,
        public readonly string $created_at,
        public readonly ?string $completed_at,
        array $children = []
    ) {
        $this->children = $children;
    }
}
