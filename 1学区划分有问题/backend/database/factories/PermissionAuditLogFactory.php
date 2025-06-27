<?php

namespace Database\Factories;

use App\Models\PermissionAuditLog;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionAuditLogFactory extends Factory
{
    protected $model = PermissionAuditLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'target_user_id' => $this->faker->boolean(70) ? User::factory() : null,
            'role_id' => $this->faker->boolean(50) ? Role::factory() : null,
            'permission_id' => $this->faker->boolean(80) ? Permission::factory() : null,
            'organization_id' => $this->faker->boolean(60) ? Organization::factory() : null,
            'action' => $this->faker->randomElement(['grant', 'revoke', 'modify', 'inherit', 'override']),
            'target_type' => $this->faker->randomElement(['user', 'role', 'organization']),
            'target_name' => $this->faker->words(2, true),
            'old_values' => $this->faker->boolean(60) ? [
                'permission_ids' => [$this->faker->numberBetween(1, 10)],
                'status' => 'active'
            ] : null,
            'new_values' => $this->faker->boolean(80) ? [
                'permission_ids' => [$this->faker->numberBetween(1, 10), $this->faker->numberBetween(11, 20)],
                'status' => 'active'
            ] : null,
            'reason' => $this->faker->boolean(70) ? $this->faker->sentence() : null,
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'status' => $this->faker->randomElement(['success', 'failed', 'pending']),
            'error_message' => $this->faker->boolean(20) ? $this->faker->sentence() : null,
        ];
    }

    public function success(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'success',
            'error_message' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error_message' => $this->faker->sentence(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'error_message' => null,
        ]);
    }

    public function grant(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'grant',
        ]);
    }

    public function revoke(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'revoke',
        ]);
    }

    public function modify(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'modify',
        ]);
    }

    public function userTarget(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_type' => 'user',
            'target_user_id' => User::factory(),
        ]);
    }

    public function roleTarget(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_type' => 'role',
            'role_id' => Role::factory(),
        ]);
    }

    public function organizationTarget(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_type' => 'organization',
            'organization_id' => Organization::factory(),
        ]);
    }
}
