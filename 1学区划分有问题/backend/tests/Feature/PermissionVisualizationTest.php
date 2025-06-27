<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use App\Models\PermissionTemplate;
use App\Models\PermissionAuditLog;
use App\Models\PermissionConflict;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class PermissionVisualizationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $organization;
    protected $permission;
    protected $role;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 创建测试数据
        $this->organization = Organization::factory()->create([
            'name' => '测试组织',
            'level' => 1
        ]);
        
        $this->permission = Permission::factory()->create([
            'name' => 'test.permission',
            'display_name' => '测试权限',
            'module' => 'test'
        ]);
        
        $this->role = Role::factory()->create([
            'name' => 'test_role',
            'display_name' => '测试角色'
        ]);
        
        $this->user = User::factory()->create([
            'name' => '测试用户',
            'primary_organization_id' => $this->organization->id
        ]);
        
        // 关联用户和组织
        $this->user->organizations()->attach($this->organization->id, ['is_primary' => true]);
        
        // 关联角色和权限
        $this->role->permissions()->attach($this->permission->id);
        
        // 关联用户和角色
        $this->user->roles()->attach($this->role->id);
        
        // 认证用户
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_get_inheritance_tree()
    {
        $response = $this->getJson('/api/permission-visualization/inheritance-tree');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data',
                    'code'
                ]);
    }

    /** @test */
    public function it_can_get_inheritance_path()
    {
        $response = $this->getJson('/api/permission-visualization/inheritance-path', [
            'organization_id' => $this->organization->id,
            'permission_id' => $this->permission->id
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'organization_id',
                        'permission_id',
                        'paths'
                    ],
                    'code'
                ]);
    }

    /** @test */
    public function it_can_detect_conflicts()
    {
        $response = $this->postJson('/api/permission-visualization/detect-conflicts', [
            'user_id' => $this->user->id
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'total_conflicts',
                        'created_records',
                        'resolved_conflicts',
                        'conflicts'
                    ],
                    'code'
                ]);
    }

    /** @test */
    public function it_can_get_permission_matrix()
    {
        $response = $this->getJson('/api/permission-visualization/permission-matrix', [
            'user_id' => $this->user->id
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data',
                    'code'
                ]);
    }

    /** @test */
    public function it_can_calculate_effective_permissions()
    {
        $response = $this->getJson('/api/permission-visualization/effective-permissions', [
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'user_id',
                        'organization_id',
                        'effective_permissions'
                    ],
                    'code'
                ]);
    }

    /** @test */
    public function it_requires_valid_user_id_for_effective_permissions()
    {
        $response = $this->getJson('/api/permission-visualization/effective-permissions');
        
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['user_id']);
    }

    /** @test */
    public function it_requires_valid_organization_and_permission_for_inheritance_path()
    {
        $response = $this->getJson('/api/permission-visualization/inheritance-path');
        
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['organization_id', 'permission_id']);
    }
}
