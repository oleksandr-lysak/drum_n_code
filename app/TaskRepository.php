<?php

declare(strict_types=1);

namespace App;

use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskRepository
{
    /**
     * Get filtered, searched and sorted tasks for a user.
     */
    public function getUserTasks(
        int $userId,
        array $filters = [],
        ?string $search = null,
        array $sort = ['created_at' => 'desc'],
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = Task::query()->where('user_id', $userId)->whereNull('parent_id');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if ($search) {
            $query->whereRaw('MATCH(title, description) AGAINST(? IN BOOLEAN MODE)', [$search]);
        }
        // Support sorting by multiple fields
        foreach ($sort as $field => $direction) {
            $query->orderBy($field, $direction);
        }
        return $query->paginate($perPage);
    }
}
