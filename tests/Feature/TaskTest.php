<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private $mockedUsers = [];
    private $mockedTasks = [];

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create();
        User::factory()->create();

        $user1 = User::first();
        $user2 = User::where('id', '!=', $user1->id)->first();

        array_push($this->mockedUsers, $user1, $user2);

        $this->actingAs($user1);

        $tasks = [
            [
                'name' => 'Task 1',
                'status' => Task::STATUS_NOT_STARTED,
                'user_id' => $user1->id,
            ],
            [
                'name' => 'Task 2',
                'status' => Task::STATUS_IN_PROGRESS,
                'user_id' => $user1->id,
            ],
            [
                'name' => 'Task 3',
                'status' => Task::STATUS_COMPLETED,
                'user_id' => $user1->id,
            ],
            [
                'name' => 'Task 4',
                'status' => Task::STATUS_COMPLETED,
                'user_id' => $user2->id,
            ],
        ];

        Task::insert($tasks);

        $this->mockedTasks = Task::with('user', 'files')
            ->get()
            ->toArray();
    }

    public function test_redirect_not_logged_in_user(): void
    {
        Auth::logout();

        $response = $this->get(route('home'));
        $response->assertStatus(302);
        $response->assertRedirect(route('auth.loginForm'));
    }

    public function test_home(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertViewIs('home');
        $response->assertViewHas('completed_count');
        $response->assertViewHas('uncompleted_count');

        $completed_count = $response->viewData('completed_count');
        $uncompleted_count = $response->viewData('uncompleted_count');

        $this->assertEquals(1, $completed_count);
        $this->assertEquals(2, $uncompleted_count);
    }

    public function test_index_without_permission(): void
    {
        $response = $this->get(route('tasks.index'));

        $response->assertStatus(200);
        $response->assertViewIs('tasks.index');

        $tasks = $response->viewData('tasks')->toArray();

        $expectedTasks = [
            $this->mockedTasks[0],
            $this->mockedTasks[1],
            $this->mockedTasks[2],
        ];

        $this->assertEquals($expectedTasks, $tasks);
    }

    public function test_index_with_right_permission(): void
    {
        Gate::shouldReceive('allows')
            ->with('viewAnyTask', Task::class)
            ->andReturn(true);
        Gate::shouldReceive('any')->andReturn(false);
        Gate::shouldReceive('check')->andReturn(false);

        $response = $this->get(route('tasks.index'));
        $response->assertStatus(200);

        $tasks = $response->viewData('tasks');
        $expectedTasks = $this->mockedTasks;

        $this->assertEquals($expectedTasks, $tasks->toArray());
    }

    public function test_create()
    {
        $response = $this->get(route('tasks.create'));

        $response->assertStatus(200);
        $response->assertViewIs('tasks.create');
        $response->assertViewHas('pageTitle');

        $pageTitle = $response->viewData('pageTitle');

        $this->assertEquals('Create Task', $pageTitle);
    }

    public function test_store_without_file()
    {
        $newTask = [
            'name' => 'New Task',
            'detail' => 'New Task Detail',
            'due_date' => date('Y-m-d', time()),
            'status' => Task::STATUS_IN_PROGRESS,
        ];

        $response = $this->post(route('tasks.store'), $newTask);

        $response->assertStatus(302);
        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tasks', $newTask);
    }

    public function test_store_with_file()
    {
        Storage::fake('public');

        $newTask = [
            'name' => 'New Task',
            'detail' => 'New Task detail',
            'due_date' => date('Y-m-d', time()),
            'status' => Task::STATUS_IN_PROGRESS,
        ];

        $file = UploadedFile::fake()->image('test_image.png');

        $response = $this->post(
            route('tasks.store'),
            array_merge($newTask, ['file' => $file])
        );

        $response->assertStatus(302);
        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tasks', $newTask);

        $task = Task::where('name', 'New Task')->first();
        $this->assertNotNull($task->files);

        $filePath = $task->files[0]->path;

        Storage::disk('public')->assertExists($filePath);
    }

    public function test_store_invalid_request()
    {
        $response = $this->post(route('tasks.store'), [
            'detail' => 'New Task',
        ]);

        $response->assertSessionHasErrors(['name', 'due_date', 'status']);
    }

    public function test_edit_task_owner()
    {
        $taskId = $this->mockedTasks[0]['id'];

        $response = $this->get(route('tasks.edit', ['id' => $taskId]));

        $response->assertStatus(200);

        $task = Task::find($taskId);
        $response->assertViewHas('task', $task);

        $response->assertSee('Edit Task');
    }

    public function test_edit_with_right_permission()
    {
        Gate::shouldReceive('denies')
            ->with('performAsTaskOwner', Task::class)
            ->andReturn(true);

        Gate::shouldReceive('authorize')
            ->with('updateAnyTask', Task::class)
            ->andReturn(true);

        Gate::shouldReceive('check')->andReturn(false);

        $taskId = $this->mockedTasks[3]['id'];
        $response = $this->get(route('tasks.edit', ['id' => $taskId]));

        $response->assertStatus(200);

        $task = Task::find($taskId);
        $response->assertViewHas('task', $task);
    }

    public function test_edit_unauthorized_user()
    {
        $taskId = $this->mockedTasks[3]['id'];
        $response = $this->get(route('tasks.edit', ['id' => $taskId]));

        $response->assertStatus(403);
    }

    public function test_update_task_owner()
    {
        $task = $this->mockedTasks[0];

        $newTaskData = [
            'name' => 'Updated Task Name',
            'detail' => 'Updated task details.',
            'due_date' => '2023-12-31',
            'status' => Task::STATUS_COMPLETED,
        ];

        $response = $this->put(
            route('tasks.update', ['id' => $task['id']]),
            $newTaskData
        );

        $response->assertStatus(302);
        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'id' => $task['id'],
            'name' => $newTaskData['name'],
            'detail' => $newTaskData['detail'],
            'due_date' => $newTaskData['due_date'],
            'status' => $newTaskData['status'],
        ]);
    }

    public function test_update_with_right_permission()
    {
        Gate::shouldReceive('denies')
            ->with('performAsTaskOwner', Task::class)
            ->andReturn(true);

        Gate::shouldReceive('authorize')
            ->with('updateAnyTask', Task::class)
            ->andReturn(true);

        Gate::shouldReceive('check')->andReturn(false);

        $task = $this->mockedTasks[3];

        $newTaskData = [
            'name' => 'Updated Task Name',
            'detail' => 'Updated task details.',
            'due_date' => '2023-12-31',
            'status' => Task::STATUS_COMPLETED,
        ];

        $response = $this->put(
            route('tasks.update', ['id' => $task['id']]),
            $newTaskData
        );

        $response->assertStatus(302);
        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'id' => $task['id'],
            'name' => $newTaskData['name'],
            'detail' => $newTaskData['detail'],
            'due_date' => $newTaskData['due_date'],
            'status' => $newTaskData['status'],
        ]);
    }

    public function test_update_unauthorized_user()
    {
        $task = $this->mockedTasks[3];

        $newTaskData = [
            'name' => 'Updated Task Name',
            'detail' => 'Updated task details.',
            'due_date' => '2023-12-31',
            'status' => Task::STATUS_COMPLETED,
        ];

        $response = $this->put(
            route('tasks.update', ['id' => $task['id']]),
            $newTaskData
        );

        $response->assertStatus(403);
    }

    public function test_delete_task_owner()
    {
        $taskId = $this->mockedTasks[0]['id'];

        $response = $this->get(route('tasks.delete', ['id' => $taskId]));

        $response->assertStatus(200);
        $response->assertViewIs('tasks.delete');

        $task = Task::find($taskId);

        $response->assertSee('Delete Task');
        $response->assertViewHas('task', $task);
    }

    public function test_delete_with_right_permission()
    {
        Gate::shouldReceive('denies')
            ->with('performAsTaskOwner', Task::class)
            ->andReturn(true);

        Gate::shouldReceive('authorize')
            ->with('deleteAnyTask', Task::class)
            ->andReturn(true);

        Gate::shouldReceive('check')->andReturn(false);

        $taskId = $this->mockedTasks[3]['id'];

        $response = $this->get(route('tasks.delete', ['id' => $taskId]));

        $response->assertStatus(200);
        $response->assertViewIs('tasks.delete');

        $task = Task::find($taskId);

        $response->assertSee('Delete Task');
        $response->assertViewHas('task', $task);
    }

    public function test_delete_unauthorized_user()
    {
        $taskId = $this->mockedTasks[3]['id'];

        $response = $this->get(route('tasks.delete', ['id' => $taskId]));

        $response->assertStatus(403);
    }

    public function test_destroy_task_owner()
    {
        $taskId = $this->mockedTasks[0]['id'];

        $response = $this->delete(route('tasks.destroy', ['id' => $taskId]));

        $response->assertStatus(302);
        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('tasks', ['id' => $taskId]);
    }

    public function test_destroy_with_right_permission()
    {
        Gate::shouldReceive('denies')
            ->with('performAsTaskOwner', Task::class)
            ->andReturn(true);

        Gate::shouldReceive('authorize')
            ->with('deleteAnyTask', Task::class)
            ->andReturn(true);

        Gate::shouldReceive('check')->andReturn(false);

        $taskId = $this->mockedTasks[3]['id'];

        $response = $this->delete(route('tasks.destroy', ['id' => $taskId]));

        $response->assertStatus(302);
        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('tasks', ['id' => $taskId]);
    }

    public function test_destroy_unauthorized_user()
    {
        $taskToDelete = $this->mockedTasks[3];

        $response = $this->delete(
            route('tasks.destroy', ['id' => $taskToDelete['id']])
        );

        $response->assertStatus(403);

        $this->assertDatabaseHas('tasks', ['id' => $taskToDelete['id']]);
    }

    public function test_progress_without_permission(): void
    {
        $response = $this->get(route('tasks.progress'));

        $response->assertStatus(200);
        $response->assertViewIs('tasks.progress');

        $tasks = $response->viewData('tasks');

        $this->assertIsArray($tasks);

        $this->assertArrayHasKey(Task::STATUS_NOT_STARTED, $tasks);
        $this->assertArrayHasKey(Task::STATUS_IN_PROGRESS, $tasks);
        $this->assertArrayHasKey(Task::STATUS_IN_REVIEW, $tasks);
        $this->assertArrayHasKey(Task::STATUS_COMPLETED, $tasks);

        $this->assertCount(1, $tasks[Task::STATUS_NOT_STARTED]);
        $this->assertCount(1, $tasks[Task::STATUS_IN_PROGRESS]);
        $this->assertCount(0, $tasks[Task::STATUS_IN_REVIEW]);
        $this->assertCount(1, $tasks[Task::STATUS_COMPLETED]);
    }

    public function test_progress_with_right_permission()
    {
        Gate::shouldReceive('allows')
            ->with('viewAnyTask', Task::class)
            ->andReturnTrue();
        Gate::shouldReceive('any')->andReturn(false);
        Gate::shouldReceive('check')->andReturn(false);

        $response = $this->get(route('tasks.progress'));

        $response->assertStatus(200);
        $response->assertViewIs('tasks.progress');
        $response->assertViewHas('tasks');

        $tasks = $response->viewData('tasks');

        $this->assertIsArray($tasks);

        $this->assertArrayHasKey(Task::STATUS_NOT_STARTED, $tasks);
        $this->assertArrayHasKey(Task::STATUS_IN_PROGRESS, $tasks);
        $this->assertArrayHasKey(Task::STATUS_IN_REVIEW, $tasks);
        $this->assertArrayHasKey(Task::STATUS_COMPLETED, $tasks);

        $this->assertCount(1, $tasks[Task::STATUS_NOT_STARTED]);
        $this->assertCount(1, $tasks[Task::STATUS_IN_PROGRESS]);
        $this->assertCount(0, $tasks[Task::STATUS_IN_REVIEW]);
        $this->assertCount(2, $tasks[Task::STATUS_COMPLETED]);
    }

    public function test_move_task_owner()
    {
        $taskId = $this->mockedTasks[0]['id'];
        $task = Task::find($taskId);

        $this->assertNotNull($task);
        $this->assertNotEquals(Task::STATUS_IN_PROGRESS, $task->status);

        $response = $this->patch(route('tasks.move', ['id' => $task->id]), [
            'status' => Task::STATUS_IN_PROGRESS,
        ]);

        $response->assertRedirect(route('tasks.progress'));

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => Task::STATUS_IN_PROGRESS,
        ]);
    }

    public function test_move_with_right_permission()
    {
        Gate::shouldReceive('denies')
            ->with('performAsTaskOwner', Task::class)
            ->andReturn(true);

        Gate::shouldReceive('authorize')
            ->with('updateAnyTask', Task::class)
            ->andReturn(true);

        Gate::shouldReceive('check')->andReturn(false);

        $taskId = $this->mockedTasks[3]['id'];
        $task = Task::find($taskId);

        $this->assertNotNull($task);
        $this->assertNotEquals(Task::STATUS_IN_PROGRESS, $task->status);

        $response = $this->patch(route('tasks.move', ['id' => $task->id]), [
            'status' => Task::STATUS_IN_PROGRESS,
        ]);

        $response->assertRedirect(route('tasks.progress'));

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => Task::STATUS_IN_PROGRESS,
        ]);
    }

    public function test_move_unauthorized_user()
    {
        $taskId = $this->mockedTasks[3]['id'];
        $task = Task::find($taskId);

        $this->assertNotNull($task);
        $this->assertNotEquals(Task::STATUS_IN_PROGRESS, $task->status);

        $response = $this->patch(route('tasks.move', ['id' => $task->id]), [
            'status' => Task::STATUS_IN_PROGRESS,
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('tasks', [
            'id' => $task['id'],
            'status' => $task['status'],
        ]);
    }

    public function test_complete_task_owner()
    {
        $taskId = $this->mockedTasks[0]['id'];
        $task = Task::find($taskId);

        $this->assertNotNull($task);

        $response = $this->patch(route('tasks.complete', ['id' => $task->id]));

        $response->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'id' => $task['id'],
            'status' => Task::STATUS_COMPLETED,
        ]);
    }

    public function test_complete_with_right_permission()
    {
        Gate::shouldReceive('denies')
            ->with('performAsTaskOwner', Task::class)
            ->andReturn(true);

        Gate::shouldReceive('authorize')
            ->with('updateAnyTask', Task::class)
            ->andReturn(true);

        Gate::shouldReceive('check')->andReturn(false);

        $taskId = $this->mockedTasks[3]['id'];
        $task = Task::find($taskId);

        $this->assertNotNull($task);

        $response = $this->patch(route('tasks.complete', ['id' => $task->id]));

        $response->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'id' => $task['id'],
            'status' => Task::STATUS_COMPLETED,
        ]);
    }

    public function test_complete_unauthorized_user()
    {
        $taskId = $this->mockedTasks[3]['id'];
        $task = Task::find($taskId);

        $response = $this->patch(route('tasks.complete', ['id' => $task->id]));

        $response->assertStatus(403);

        $this->assertDatabaseHas('tasks', [
            'id' => $task['id'],
            'status' => $task['status'],
        ]);
    }
}
