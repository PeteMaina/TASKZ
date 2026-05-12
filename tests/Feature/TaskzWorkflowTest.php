<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskzWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_reach_empty_dashboard_after_registering(): void
    {
        $response = $this->post('/register', [
            'name' => 'Alex Kimani',
            'email' => 'alex@example.test',
            'timezone' => 'Africa/Nairobi',
            'password' => 'hunter2-but-longer',
            'password_confirmation' => 'hunter2-but-longer',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();

        $this->get('/')
            ->assertOk()
            ->assertSee('Create your first workspace');
    }

    public function test_user_can_create_workspace(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/workspaces', ['name' => 'Acme Workspace'])
            ->assertRedirect();

        $this->assertDatabaseHas('workspaces', ['name' => 'Acme Workspace']);
        $this->assertDatabaseHas('workspace_members', ['user_id' => $user->id, 'role' => 'owner']);
    }
}
