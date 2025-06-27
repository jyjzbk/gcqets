<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class OrganizationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 创建测试数据
        $this->seed([
            OrganizationSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class
        ]);

        $this->admin = User::where('email', 'admin@example.com')->first();
        $this->organization = Organization::first();
    }

    public function test_can_list_organizations()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/organizations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'code',
                        'type',
                        'level',
                        'parent_id',
                        'status'
                    ]
                ]
            ]);
    }

    public function test_can_create_organization()
    {
        $data = [
            'name' => $this->faker->company,
            'code' => $this->faker->unique()->regexify('[A-Z]{2}-[A-Z]{3}'),
            'type' => 'school',
            'level' => 5,
            'parent_id' => $this->organization->id,
            'status' => 'active'
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/organizations', $data);

        $response->assertStatus(201)
            ->assertJsonFragment($data);
    }

    public function test_can_update_organization()
    {
        $data = [
            'name' => $this->faker->company,
            'status' => 'inactive'
        ];

        $response = $this->actingAs($this->admin)
            ->putJson("/api/organizations/{$this->organization->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment($data);
    }

    public function test_can_delete_organization()
    {
        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/organizations/{$this->organization->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('organizations', ['id' => $this->organization->id]);
    }
}