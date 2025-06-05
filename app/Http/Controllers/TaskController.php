<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Task;
use App\TaskRepository;
use App\TaskService;
use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Http\Resources\TaskResource;
use App\Http\Resources\TaskCollection;
use App\Http\Requests\TaskIndexRequest;
use App\IncompleteTasksException;
use DomainException;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Tasks",
 *     description="Task management endpoints"
 * )
 */
class TaskController extends Controller
{
    public function __construct(
        protected TaskRepository $repository,
        protected TaskService $service
    ) {
    }

    /**
     * @OA\Get(
     *     path="/api/tasks",
     *     summary="Get a list of user's tasks",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"todo","done"})),
     *     @OA\Parameter(name="priority", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=5)),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort", in="query", required=false, @OA\Schema(type="string", example="priority:desc,created_at:asc")),
     *     @OA\Response(
     *         response=200,
     *         description="List of tasks",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Task")
     *             )
     *         )
     *     )
     * )
     */
    public function index(TaskIndexRequest $request): TaskCollection
    {
        $dtos = $this->service->getUserTasks(
            $request->user()->id,
            $request->filters(),
            $request->search(),
            TaskService::parseSort($request->sort())
        );
        return new TaskCollection($dtos);
    }

    /**
     * @OA\Post(
     *     path="/api/tasks",
     *     summary="Create a new task",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/TaskCreateRequest")),
     *     @OA\Response(response=201, description="Task created", @OA\JsonContent(ref="#/components/schemas/Task"))
     * )
     */
    public function store(TaskStoreRequest $request): JsonResponse
    {
        $dto = $request->toDTO($request->user()->id);
        $taskDTO = $this->service->create($dto);
        return TaskResource::created($taskDTO);
    }

    /**
     * @OA\Get(
     *     path="/api/tasks/{id}",
     *     summary="Get a single task with subtasks",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Task", @OA\JsonContent(ref="#/components/schemas/Task")),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);
        $dto = $this->service->mapToDTO($this->service->getTree($task));
        return (new TaskResource($dto))->response();
    }

    /**
     * @OA\Put(
     *     path="/api/tasks/{id}",
     *     summary="Update a task",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/TaskUpdateRequest")),
     *     @OA\Response(response=200, description="Task updated", @OA\JsonContent(ref="#/components/schemas/Task")),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=400, description="Cannot change status from done to another status"),
     *     @OA\Response(response=409, description="Task has incomplete subtasks", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Task has incomplete subtasks"),
     *         @OA\Property(property="incomplete_tasks", type="array", @OA\Items(type="object", @OA\Property(property="id", type="integer"), @OA\Property(property="title", type="string"))),
     *     ))
     * )
     */
    public function update(TaskUpdateRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);
        try {
            $dto = $request->toDTO($task);
            $taskDTO = $this->service->update($task, $dto);
            return (new TaskResource($taskDTO))->response();
        } catch (IncompleteTasksException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'incomplete_tasks' => $e->incompleteTasks,
            ], 409);
        } catch (DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/tasks/{id}/done",
     *     summary="Mark task as done",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Task marked as done", @OA\JsonContent(ref="#/components/schemas/Task")),
     *     @OA\Response(response=400, description="Task cannot be marked as done"),
     *     @OA\Response(response=409, description="Task has incomplete subtasks", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Task has incomplete subtasks"),
     *         @OA\Property(property="incomplete_tasks", type="array", @OA\Items(type="object", @OA\Property(property="id", type="integer"), @OA\Property(property="title", type="string"))),
     *     ))
     * )
     */
    public function markAsDone(Task $task): JsonResponse
    {
        $this->authorize('update', $task);
        try {
            $this->service->markAsDone($task);
        } catch (IncompleteTasksException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'incomplete_tasks' => $e->incompleteTasks,
            ], 409);
        } catch (DomainException $e) {
            abort(400, $e->getMessage());
        }
        $dto = $this->service->mapToDTO($this->service->getTree($task->refresh()));
        return (new TaskResource($dto))->response();
    }

    /**
     * @OA\Delete(
     *     path="/api/tasks/{id}",
     *     summary="Delete a task",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Task deleted"),
     *     @OA\Response(response=400, description="Cannot delete completed task")
     * )
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);
        try {
            $this->service->delete($task);
            return response()->json(['message' => 'Task deleted.']);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
