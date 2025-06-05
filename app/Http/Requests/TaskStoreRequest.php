<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTO\TaskDTO;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="TaskCreateRequest",
 *   type="object",
 *   required={"status","priority","title"},
 *   @OA\Property(property="status", type="string", enum={"todo","done"}, example="todo"),
 *   @OA\Property(property="priority", type="integer", minimum=1, maximum=5, example=3),
 *   @OA\Property(property="title", type="string", example="New Task"),
 *   @OA\Property(property="description", type="string", nullable=true, example="Description"),
 *   @OA\Property(property="parent_id", type="integer", nullable=true, example=null)
 * )
 */

class TaskStoreRequest extends FormRequest
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
            'status' => 'required|in:todo,done',
            'priority' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:tasks,id',
        ];
    }

    public function toDTO(int $userId): TaskDTO
    {
        $data = $this->validated();
        return new TaskDTO(
            id: null,
            user_id: $userId,
            parent_id: $data['parent_id'] ?? null,
            status: \App\TaskStatusEnum::from($data['status']),
            priority: \App\TaskPriorityEnum::from((int)$data['priority']),
            title: $data['title'],
            description: $data['description'] ?? null,
            created_at: now()->toISOString(),
            completed_at: null,
            children: []
        );
    }
}
