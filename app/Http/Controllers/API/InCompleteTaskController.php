<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;

/**
 * @OA\Post(
 *     path="/api/tasks/incomplete/{taskId}",
 *     summary="Mark a task as done",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="taskId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         description="ID of the task to mark as done"
 *     ),
 *     @OA\Response(response="202", description="Task marked as done successfully", @OA\JsonContent(ref="#/components/schemas/TaskResource")),
 *     @OA\Response(response="404", description="Task not found"),
 *     @OA\Response(response="500", description="Error marking the task as done")
 * )
 */
class InCompleteTaskController extends Controller
{
    function __invoke(Request $request, string $id)
    {
        $task = Task::find($id);
        if (!$task)
            return response()->json([
                'status' => false,
                'message' => 'Task not found',
            ], 404);
        try {
            $task->update(['status' => 'to_do']);
            $tasks = TaskResource::collection($request->user()->tasks->sortByDesc('created_at'));
            return response()->json([
                'status' => true,
                'message' => 'Task marked todo with success.',
                'tasks' => $tasks,
            ], 202);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating the task',
            ], 500);
        }
    }
}
