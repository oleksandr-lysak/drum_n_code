<?php

declare(strict_types=1);

namespace App;

use App\Models\Task;
use App\TaskStatusEnum;
use App\TaskRepository;
use App\IncompleteTasksException;
use App\DTO\TaskDTO;

class TaskService
{
    public function __construct(private TaskRepository $repository)
    {
    }

    /**
     * Collect incomplete descendants of a task.
     */
    private function collectIncompleteDescendants(Task $task, array &$result = []): void
    {
        $task->loadMissing('children');
        foreach ($task->children as $child) {
            if ($child->status !== TaskStatusEnum::DONE) {
                $result[] = [
                    'id' => $child->id,
                    'title' => $child->title,
                ];
            }
            $this->collectIncompleteDescendants($child, $result);
        }
    }

    /**
     * Mark task as done if all subtasks are done.
     */
    public function markAsDone(Task $task): void
    {
        if ($task->status === TaskStatusEnum::DONE) {
            throw new \DomainException('Task is already completed.');
        }
        $incomplete = [];
        $this->collectIncompleteDescendants($task, $incomplete);
        if (!empty($incomplete)) {
            throw new IncompleteTasksException('Task has incomplete subtasks.', $incomplete);
        }
        $task->status = TaskStatusEnum::DONE;
        $task->completed_at = now();
        $task->save();
    }

    /**
     * Delete task if not done. Throws exception if not allowed.
     */
    public function delete(Task $task): void
    {
        if ($task->status === TaskStatusEnum::DONE) {
            throw new \DomainException('Cannot delete completed task.');
        }
        $task->delete();
    }

    /**
     * Create a new task from DTO.
     */
    public function create(TaskDTO $dto): TaskDTO
    {
        $task = Task::create([
            'user_id' => $dto->user_id,
            'parent_id' => $dto->parent_id,
            'status' => $dto->status,
            'priority' => $dto->priority,
            'title' => $dto->title,
            'description' => $dto->description,
        ]);
        return $this->mapToDTO($this->getTree($task));
    }

    /**
     * Update a task from DTO.
     */
    public function update(Task $task, TaskDTO $dto): TaskDTO
    {
        // Forbidden to change status from done to another status
        if ($task->status === TaskStatusEnum::DONE && $dto->status !== TaskStatusEnum::DONE) {
            throw new \DomainException('Cannot change status from done to another status.');
        }
        // Check if status is changed from not done to done
        if ($task->status !== TaskStatusEnum::DONE && $dto->status === TaskStatusEnum::DONE) {
            $incomplete = [];
            $this->collectIncompleteDescendants($task, $incomplete);
            if (!empty($incomplete)) {
                throw new IncompleteTasksException('Task has incomplete subtasks.', $incomplete);
            }
        }
        $task->update([
            'parent_id' => $dto->parent_id,
            'status' => $dto->status,
            'priority' => $dto->priority,
            'title' => $dto->title,
            'description' => $dto->description,
        ]);
        return $this->mapToDTO($this->getTree($task));
    }

    /**
     * Get a task with recursive children tree.
     */
    public function getTree(Task $task): Task
    {
        $task->load(['children' => function ($query) {
            $query->with('children');
        }]);
        return $task;
    }

    /**
     * Map Task (with children) to TaskDTO (recursively).
     */
    public function mapToDTO(Task $task): TaskDTO
    {
        $children = $task->relationLoaded('children')
            ? $task->children->map(fn($child) => $this->mapToDTO($child))->all()
            : [];
        return new TaskDTO(
            id: $task->id,
            user_id: $task->user_id,
            parent_id: $task->parent_id,
            status: $task->status,
            priority: $task->priority,
            title: $task->title,
            description: $task->description,
            created_at: $task->created_at->toISOString(),
            completed_at: $task->completed_at?->toISOString(),
            children: $children
        );
    }

    /**
     * Get filtered, searched and sorted tasks for a user as DTOs.
     */
    public function getUserTasks(
        int $userId,
        array $filters = [],
        ?string $search = null,
        array $sort = ['created_at' => 'desc']
    ): array {
        $tasks = $this->repository->getUserTasks($userId, $filters, $search, $sort);
        return array_map(
            fn($task) => $this->mapToDTO($this->getTree($task)),
            $tasks->items()
        );
    }

    public static function parseSort(?string $sort): array
    {
        if (!$sort) {
            return ['created_at' => 'desc'];
        }
        $aliases = [
            'createdat' => 'created_at',
            'completedat' => 'completed_at',
            'priority' => 'priority',
        ];
        $result = [];
        foreach (explode(',', $sort) as $part) {
            $part = trim($part);
            if (empty($part)) {
                continue;
            }
            if (preg_match('/^([a-zA-Z0-9_]+)[\s:]+(asc|desc)$/i', $part, $m)) {
                $field = strtolower($m[1]);
                $field = $aliases[$field] ?? $field;
                $result[$field] = strtolower($m[2]);
            } else {
                $field = strtolower($part);
                $field = $aliases[$field] ?? $field;
                $result[$field] = 'asc';
            }
        }
        return $result;
    }
}
