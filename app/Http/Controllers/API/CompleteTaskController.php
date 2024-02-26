<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Task;

class CompleteTaskController extends Controller
{
    function __invoke(Task $task)
    {
        if (!$task)
            return response()->json([
                'status' => 'error',
                'message' => 'Task not found',
            ], 404);

        if ($task->update(['status' => "done"]))
            return response()->json([
                'status' => 'success',
                'message' => 'Task marked done with success.',
                'task' => $task->refresh(),
            ], 202);
        return response()->json([
            'status' => 'error',
            'message' => 'Error withing markeing the task completed.',
        ]);
    }
}
