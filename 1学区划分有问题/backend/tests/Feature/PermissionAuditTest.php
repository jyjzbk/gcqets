<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Organization;
use App\Models\PermissionAuditLog;
use App\Models\PermissionConflict;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class PermissionAuditTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $permission;
    protected $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->organization = Organization::factory()->create();
        $this->permission = Permission::factory()->create();
        $this->user = User::factory()->create();
        
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_list_audit_logs()
    {
        PermissionAuditLog::factory()->create([
            'user_id' => $this->user->id,
            'permission_id' => $this->permission->id,
            'action' => 'grant',
            'target_type' => 'user'
        ]);

        $response = $this->getJson('/api/permission-audit/logs');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'action',
                                'target_type',
                                'created_at',
                                'action_description',
                                'changes_summary'
                            ]
                        ]
                    ],
                    'code'
                ]);
    }

    /** @test */
    public function it_can_show_audit_log_detail()
    {
        $log = PermissionAuditLog::factory()->create([
            'user_id' => $this->user->id,
            'permission_id' => $this->permission->id
        ]);

        $response = $this->getJson("/api/permission-audit/logs/{$log->id}");
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'action',
                        'target_type',
                        'action_description',
                        'changes_summary'
                    ],
                    'code'
                ]);
    }

    /** @test */
    public function it_can_get_user_stats()
    {
        PermissionAuditLog::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'action' => 'grant'
        ]);

        $response = $this->getJson('/api/permission-audit/user-stats', [
            'user_id' => $this->user->id,
            'days' => 30
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'user_id',
                        'days',
                        'stats'
                    ],
                    'code'
                ]);
    }

    /** @test */
    public function it_can_get_organization_stats()
    {
        PermissionAuditLog::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'action' => 'modify'
        ]);

        $response = $this->getJson('/api/permission-audit/organization-stats', [
            'organization_id' => $this->organization->id,
            'days' => 30
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'organization_id',
                        'days',
                        'stats'
                    ],
                    'code'
                ]);
    }

    /** @test */
    public function it_can_get_permission_hotspots()
    {
        PermissionAuditLog::factory()->count(10)->create([
            'permission_id' => $this->permission->id,
            'action' => 'grant'
        ]);

        $response = $this->getJson('/api/permission-audit/permission-hotspots', [
            'days' => 30
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'days',
                        'hotspots'
                    ],
                    'code'
                ]);
    }

    /** @test */
    public function it_can_list_conflicts()
    {
        PermissionConflict::factory()->create([
            'user_id' => $this->user->id,
            'permission_id' => $this->permission->id,
            'conflict_type' => 'role_user',
            'status' => 'unresolved'
        ]);

        $response = $this->getJson('/api/permission-audit/conflicts');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'conflict_type',
                                'status',
                                'priority',
                                'conflict_description',
                                'priority_label',
                                'status_label'
                            ]
                        ]
                    ],
                    'code'
                ]);
    }

    /** @test */
    public function it_can_resolve_conflict()
    {
        $conflict = PermissionConflict::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'unresolved'
        ]);

        $response = $this->postJson("/api/permission-audit/conflicts/{$conflict->id}/resolve", [
            'strategy' => 'allow',
            'notes' => '手动解决冲突'
        ]);
        
        $response->assertStatus(200);

        $this->assertDatabaseHas('permission_conflicts', [
            'id' => $conflict->id,
            'status' => 'resolved',
            'resolution_strategy' => 'allow'
        ]);
    }

    /** @test */
    public function it_can_ignore_conflict()
    {
        $conflict = PermissionConflict::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'unresolved'
        ]);

        $response = $this->postJson("/api/permission-audit/conflicts/{$conflict->id}/ignore", [
            'notes' => '忽略此冲突'
        ]);
        
        $response->assertStatus(200);

        $this->assertDatabaseHas('permission_conflicts', [
            'id' => $conflict->id,
            'status' => 'ignored'
        ]);
    }

    /** @test */
    public function it_can_get_conflict_stats()
    {
        PermissionConflict::factory()->count(5)->create(['status' => 'unresolved']);
        PermissionConflict::factory()->count(3)->create(['status' => 'resolved']);

        $response = $this->getJson('/api/permission-audit/conflict-stats');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'total',
                        'unresolved',
                        'by_type',
                        'by_priority'
                    ],
                    'code'
                ]);
    }

    /** @test */
    public function it_validates_required_fields_for_user_stats()
    {
        $response = $this->getJson('/api/permission-audit/user-stats');
        
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['user_id']);
    }

    /** @test */
    public function it_validates_required_fields_for_organization_stats()
    {
        $response = $this->getJson('/api/permission-audit/organization-stats');
        
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['organization_id']);
    }

    /** @test */
    public function it_can_filter_logs_by_action()
    {
        PermissionAuditLog::factory()->create(['action' => 'grant']);
        PermissionAuditLog::factory()->create(['action' => 'revoke']);

        $response = $this->getJson('/api/permission-audit/logs', [
            'action' => 'grant'
        ]);
        
        $response->assertStatus(200);
        
        $logs = $response->json('data.data');
        $this->assertCount(1, $logs);
        $this->assertEquals('grant', $logs[0]['action']);
    }

    /** @test */
    public function it_can_filter_logs_by_date_range()
    {
        PermissionAuditLog::factory()->create([
            'created_at' => now()->subDays(5)
        ]);
        PermissionAuditLog::factory()->create([
            'created_at' => now()->subDays(15)
        ]);

        $response = $this->getJson('/api/permission-audit/logs', [
            'start_date' => now()->subDays(10)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d')
        ]);
        
        $response->assertStatus(200);
        
        $logs = $response->json('data.data');
        $this->assertCount(1, $logs);
    }
}
