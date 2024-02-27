<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(
 *     name="Tasks",
 *     description="Operations related to tasks"
 * )
 */
class TaskController extends Controller
{

    function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * @OA\Get(
     *     path="/api/tasks",
     *     summary="Get a list of tasks for the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="201", description="List of tasks", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/TaskResource"))),
     * )
     */
    public function index()
    {
        $user = auth()->user();
        $tasks = TaskResource::collection($user->tasks);
        return response()->json($tasks, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/tasks/{taskId}",
     *     summary="Get details of a specific task",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="ID of the task to retrieve"
     *     ),
     *     @OA\Response(response="200", description="Task details", @OA\JsonContent(ref="#/components/schemas/TaskResource")),
     *     @OA\Response(response="404", description="Task not found"),
     * )
     */
    public function show(string $id)
    {
        $task = Task::find($id);
        $task = new TaskResource($task);
        if ($task) {
            return response()->json($task);
        }
    }

     /**
     * @OA\Post(
     *     path="/api/tasks",
     *     summary="Create a new task",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "description"},
     *             @OA\Property(property="title", type="string", minLength=3),
     *             @OA\Property(property="description", type="string", minLength=5),
     *         )
     *     ),
     *     @OA\Response(response="201", description="Task created successfully", @OA\JsonContent(ref="#/components/schemas/TaskResource")),
     *     @OA\Response(response="400", description="Error creating the task"),
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|min:3',
            'description' => 'string|min:5',
        ]);

        $task = $request->user()->tasks()->create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'to_do',
        ]);
        $task = new TaskResource($task);
        if ($task) {
            return response()->json([
                'status' => 'success',
                'message' => 'Task created successfully',
                'task' => $task,
            ], 201);
        }

        return response()->json([
            'error' => 'error',
            'message' => 'Error withing creating the task',
        ], 400);
    }

    /**
     * @OA\Put(
     *     path="/api/tasks/{taskId}",
     *     summary="Update an existing task",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="ID of the task to update"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", minLength=3),
     *             @OA\Property(property="description", type="string", minLength=5),
     *             @OA\Property(property="status", type="string", enum={"to_do", "in_progress", "done"}),
     *         )
     *     ),
     *     @OA\Response(response="202", description="Task updated successfully", @OA\JsonContent(ref="#/components/schemas/TaskResource")),
     *     @OA\Response(response="400", description="Error updating the task"),
     *     @OA\Response(response="404", description="Task not found"),
     * )
     */
    public function update(Request $request, Task $task)
    {
        if (!$task){
            return response()->json([
                'error' => 'error',
                'message' => 'Task not found',
            ], 404);
        }
        $data = $request->validate([
            'title' => 'string|min:3',
            'description' => 'string|min:5',
            'status' => ['required', 'string', Rule::in(['to_do', 'in_progress', 'done'])],
        ]);
        $is_updated = $task->update($data);
        if ($is_updated)
            return response()->json([
                'status' => 'success',
                'message' => 'Task updated successfully',
                'task' => new TaskResource($task->refresh()),
            ], 202);
        return response()->json([
            'error' => 'error',
            'message' => 'Error withing updating the task',
        ], 400);
    }

    /**
     * @OA\Delete(
     *     path="/api/tasks/{taskId}",
     *     summary="Delete a task",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="ID of the task to delete"
     *     ),
     *     @OA\Response(response="200", description="Task deleted successfully", @OA\JsonContent(ref="#/components/schemas/TaskResource")),
     *     @OA\Response(response="404", description="Task not found"),
     * )
     */
    public function destroy(Task $task)
    {
        if ($task){
            $task->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Task deleted successfully',
                'task' => new TaskResource($task),
            ]);
        }
        return response()->json([
            'status' => 'error',
            'message' => 'Task not found!',
        ], 404);
    }
}
