<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTO\TaskDTO;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="TaskUpdateRequest",
 *   type="object",
 *   @OA\Property(property="status", type="string", enum={"todo","done"}, example="done"),
 *   @OA\Property(property="priority", type="integer", minimum=1, maximum=5, example=2),
 *   @OA\Property(property="title", type="string", example="Updated Task"),
 *   @OA\Property(property="description", type="string", nullable=true, example="Updated description"),
 *   @OA\Property(property="parent_id", type="integer", nullable=true, example=null)
 * )
 */

class TaskUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'sometimes|in:todo,done',
            'priority' => 'sometimes|integer|min:1|max:5',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:tasks,id',
        ];
    }

    public function toDTO(\App\Models\Task $task): TaskDTO
    {
        $data = $this->validated();
        return new TaskDTO(
            id: $task->id,
            user_id: $task->user_id,
            parent_id: $data['parent_id'] ?? $task->parent_id,
            status: isset($data['status']) ? \App\TaskStatusEnum::from($data['status']) : $task->status,
            priority: isset($data['priority']) ? \App\TaskPriorityEnum::from((int)$data['priority']) : $task->priority,
            title: $data['title'] ?? $task->title,
            description: $data['description'] ?? $task->description,
            created_at: $task->created_at->toISOString(),
            completed_at: $task->completed_at?->toISOString(),
            children: []
        );
    }
}
