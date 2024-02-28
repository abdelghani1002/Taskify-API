<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompleteTaskControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_mark_task_as_done()
    {
        // Create a user and a task
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id, 'status' => 'to_do']);

        // Send a request to mark the task as done
        $response = $this->actingAs($user, 'api')->post("/api/tasks/complete/{$task->id}");

        // Assert the response status and structure
        $response->assertStatus(202)
            ->assertJsonStructure([
                'status',
                'message',
                'task' => [
                    'id',
                    'title',
                    'description',
                    'status',
                ],
            ])->assertJson([
                'status' => 'success',
                'message' => 'Task marked done with success.',
                'task' => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => 'done',
                ],
            ]);

        // Refresh the task from the database to get the latest status
        $task->refresh();

        // Assert that the task is marked as done in the database
        $this->assertEquals('done', $task->status);
    }

    public function test_cannot_mark_nonexistent_task_as_done()
    {
        // Create a user
        $user = User::factory()->create();

        // Send a request to mark a nonexistent task as done
        $response = $this->actingAs($user, 'api')->post("/api/tasks/complete/999");

        // Assert the response status and structure, and that the error message indicates the task was not found
        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Task not found'
            ]);
    }
}
