<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use App\Models\PermissionTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class PermissionTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $permission;
    protected $role;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->permission = Permission::factory()->create([
            'name' => 'test.permission',
            'display_name' => '测试权限'
        ]);
        
        $this->role = Role::factory()->create([
            'name' => 'test_role',
            'display_name' => '测试角色'
        ]);
        
        $this->user = User::factory()->create([
            'name' => '测试用户'
        ]);
        
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_list_permission_templates()
    {
        PermissionTemplate::factory()->create([
            'name' => 'test_template',
            'display_name' => '测试模板',
            'template_type' => 'role'
        ]);

        $response = $this->getJson('/api/permission-templates');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'name',
                                'display_name',
                                'template_type',
                                'permission_count'
                            ]
                        ]
                    ],
                    'code'
                ]);
    }

    /** @test */
    public function it_can_create_permission_template()
    {
        $templateData = [
            'name' => 'new_template',
            'display_name' => '新模板',
            'description' => '测试模板描述',
            'template_type' => 'role',
            'target_level' => 1,
            'permission_ids' => [$this->permission->id],
            'status' => 'active'
        ];

        $response = $this->postJson('/api/permission-templates', $templateData);
        
        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'display_name',
                        'template_type'
                    ],
                    'code'
                ]);

        $this->assertDatabaseHas('permission_templates', [
            'name' => 'new_template',
            'display_name' => '新模板',
            'template_type' => 'role'
        ]);
    }

    /** @test */
    public function it_can_show_permission_template()
    {
        $template = PermissionTemplate::factory()->create([
            'name' => 'test_template',
            'display_name' => '测试模板'
        ]);

        $response = $this->getJson("/api/permission-templates/{$template->id}");
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'display_name',
                        'permission_objects'
                    ],
                    'code'
                ]);
    }

    /** @test */
    public function it_can_update_permission_template()
    {
        $template = PermissionTemplate::factory()->create([
            'name' => 'test_template',
            'display_name' => '测试模板',
            'is_system' => false
        ]);

        $updateData = [
            'name' => 'updated_template',
            'display_name' => '更新的模板',
            'description' => '更新的描述',
            'template_type' => 'role',
            'permission_ids' => [$this->permission->id],
            'status' => 'active'
        ];

        $response = $this->putJson("/api/permission-templates/{$template->id}", $updateData);
        
        $response->assertStatus(200);

        $this->assertDatabaseHas('permission_templates', [
            'id' => $template->id,
            'name' => 'updated_template',
            'display_name' => '更新的模板'
        ]);
    }

    /** @test */
    public function it_can_delete_permission_template()
    {
        $template = PermissionTemplate::factory()->create([
            'name' => 'test_template',
            'is_system' => false
        ]);

        $response = $this->deleteJson("/api/permission-templates/{$template->id}");
        
        $response->assertStatus(200);

        $this->assertSoftDeleted('permission_templates', [
            'id' => $template->id
        ]);
    }

    /** @test */
    public function it_cannot_delete_system_template()
    {
        $template = PermissionTemplate::factory()->create([
            'name' => 'system_template',
            'is_system' => true
        ]);

        $response = $this->deleteJson("/api/permission-templates/{$template->id}");
        
        $response->assertStatus(403);

        $this->assertDatabaseHas('permission_templates', [
            'id' => $template->id
        ]);
    }

    /** @test */
    public function it_can_apply_template_to_role()
    {
        $template = PermissionTemplate::factory()->create([
            'template_type' => 'role',
            'permission_ids' => [$this->permission->id]
        ]);

        $response = $this->postJson("/api/permission-templates/{$template->id}/apply-to-role", [
            'role_id' => $this->role->id
        ]);
        
        $response->assertStatus(200);

        $this->assertTrue($this->role->permissions()->where('permission_id', $this->permission->id)->exists());
    }

    /** @test */
    public function it_can_apply_template_to_user()
    {
        $template = PermissionTemplate::factory()->create([
            'template_type' => 'user',
            'permission_ids' => [$this->permission->id]
        ]);

        $response = $this->postJson("/api/permission-templates/{$template->id}/apply-to-user", [
            'user_id' => $this->user->id
        ]);
        
        $response->assertStatus(200);

        $this->assertTrue($this->user->permissions()->where('permission_id', $this->permission->id)->exists());
    }

    /** @test */
    public function it_can_duplicate_template()
    {
        $template = PermissionTemplate::factory()->create([
            'name' => 'original_template',
            'display_name' => '原始模板'
        ]);

        $response = $this->postJson("/api/permission-templates/{$template->id}/duplicate", [
            'name' => 'duplicated_template',
            'display_name' => '复制的模板'
        ]);
        
        $response->assertStatus(201);

        $this->assertDatabaseHas('permission_templates', [
            'name' => 'duplicated_template',
            'display_name' => '复制的模板',
            'is_system' => false
        ]);
    }

    /** @test */
    public function it_can_get_recommended_templates()
    {
        PermissionTemplate::factory()->create([
            'template_type' => 'role',
            'target_level' => 1,
            'status' => 'active'
        ]);

        $response = $this->getJson('/api/permission-templates/recommended', [
            'target_type' => 'role',
            'target_level' => 1
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data',
                    'code'
                ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_template()
    {
        $response = $this->postJson('/api/permission-templates', []);
        
        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'name',
                    'display_name',
                    'template_type',
                    'permission_ids'
                ]);
    }

    /** @test */
    public function it_validates_unique_template_name()
    {
        PermissionTemplate::factory()->create([
            'name' => 'existing_template'
        ]);

        $response = $this->postJson('/api/permission-templates', [
            'name' => 'existing_template',
            'display_name' => '测试模板',
            'template_type' => 'role',
            'permission_ids' => [$this->permission->id]
        ]);
        
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }
}
