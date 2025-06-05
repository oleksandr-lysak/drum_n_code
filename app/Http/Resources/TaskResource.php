<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\DTO\TaskDTO;

/**
 * @OA\Schema(
 *   schema="TaskResource",
 *   type="object",
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
class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        /** @var TaskDTO $this->resource */
        return [
            'id' => $this->resource->id,
            'user_id' => $this->resource->user_id,
            'parent_id' => $this->resource->parent_id,
            'status' => $this->resource->status->value,
            'priority' => $this->resource->priority->value,
            'title' => $this->resource->title,
            'description' => $this->resource->description,
            'created_at' => $this->resource->created_at,
            'completed_at' => $this->resource->completed_at,
            'children' => TaskResource::collection($this->resource->children),
        ];
    }

    public static function created($dto)
    {
        return (new static($dto))->response()->setStatusCode(201);
    }
}
