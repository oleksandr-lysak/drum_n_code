<?php

declare(strict_types=1);

namespace App\Http\Resources;

/**
 * @OA\Schema(
 *   schema="TaskCollection",
 *   type="object",
 *   @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/TaskResource"))
 * )
 */
use Illuminate\Http\Resources\Json\ResourceCollection;

class TaskCollection extends ResourceCollection
{
    public $collects = TaskResource::class;

    public function toArray($request): array
    {
        return [
            'data' => $this->collection,
        ];
    }
}
