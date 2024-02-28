<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_can_get_list_of_tasks()
    {
        $user = User::factory()->create();
        Task::factory(5)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')->get('/api/tasks');

        $response->assertStatus(201)
            ->assertJsonCount(5, 'data');
    }

    public function test_can_create_task()
    {
        $user = User::factory()->create();
        $data = [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
        ];

        $response = $this->actingAs($user, 'api')->post('/api/tasks', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['status' => 'success']);
    }

    public function test_can_show_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')->get("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $task->id]);
    }

    public function test_can_update_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);
        $data = ['title' => $this->faker->sentence];

        $response = $this->actingAs($user, 'api')->put("/api/tasks/{$task->id}", $data);

        $response->assertStatus(202)
            ->assertJsonFragment(['status' => 'success']);
    }

    public function test_can_delete_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')->delete("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'success']);
    }
}
