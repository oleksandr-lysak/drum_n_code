<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\User;
use App\TaskStatusEnum;
use App\TaskPriorityEnum;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user1 = User::where('email', 'user1@example.com')->first();
        $user2 = User::where('email', 'user2@example.com')->first();

        // Головна задача user1
        $task1 = Task::create([
            'user_id' => $user1->id,
            'status' => TaskStatusEnum::TODO,
            'priority' => TaskPriorityEnum::P3,
            'title' => 'Main Task 1',
            'description' => 'Main task for user1',
        ]);
        // Підзадача для task1
        $subtask1 = Task::create([
            'user_id' => $user1->id,
            'parent_id' => $task1->id,
            'status' => TaskStatusEnum::TODO,
            'priority' => TaskPriorityEnum::P2,
            'title' => 'Subtask 1.1',
            'description' => 'Subtask for main task 1',
        ]);
        // Ще одна задача user1
        Task::create([
            'user_id' => $user1->id,
            'status' => TaskStatusEnum::DONE,
            'priority' => TaskPriorityEnum::P1,
            'title' => 'Completed Task',
            'description' => 'Already done',
            'completed_at' => now(),
        ]);
        // Задача user2
        Task::create([
            'user_id' => $user2->id,
            'status' => TaskStatusEnum::TODO,
            'priority' => TaskPriorityEnum::P4,
            'title' => 'User2 Task',
            'description' => 'Task for user2',
        ]);
    }
}
