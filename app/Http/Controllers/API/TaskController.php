<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{

    function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $tasks = TaskResource::collection($user->tasks);
        return response()->json($tasks, 201);
    }

    /**
     * Show the profile for a given user.
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
     * Store a newly created resource in storage.
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
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
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
