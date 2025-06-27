<?php

namespace Database\Factories;

use App\Models\PermissionConflict;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionConflictFactory extends Factory
{
    protected $model = PermissionConflict::class;

    public function definition(): array
    {
        return [
            'user_id' => $this->faker->boolean(80) ? User::factory() : null,
            'role_id' => $this->faker->boolean(60) ? Role::factory() : null,
            'organization_id' => $this->faker->boolean(70) ? Organization::factory() : null,
            'permission_id' => Permission::factory(),
            'conflict_type' => $this->faker->randomElement(['role_user', 'role_role', 'inheritance', 'explicit_deny']),
            'conflict_sources' => [
                [
                    'source_type' => 'role',
                    'source_id' => $this->faker->numberBetween(1, 10),
                    'source_name' => $this->faker->words(2, true)
                ],
                [
                    'source_type' => 'direct',
                    'source_id' => null,
                    'source_name' => '直接分配'
                ]
            ],
            'resolution_strategy' => $this->faker->randomElement(['allow', 'deny', 'manual']),
            'priority' => $this->faker->randomElement(['high', 'medium', 'low']),
            'status' => $this->faker->randomElement(['unresolved', 'resolved', 'ignored']),
            'resolved_by' => $this->faker->boolean(30) ? User::factory() : null,
            'resolved_at' => $this->faker->boolean(30) ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
            'resolution_notes' => $this->faker->boolean(40) ? $this->faker->sentence() : null,
        ];
    }

    public function unresolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'unresolved',
            'resolved_by' => null,
            'resolved_at' => null,
            'resolution_notes' => null,
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'resolved_by' => User::factory(),
            'resolved_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'resolution_notes' => $this->faker->sentence(),
        ]);
    }

    public function ignored(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ignored',
            'resolved_by' => User::factory(),
            'resolved_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'resolution_notes' => $this->faker->sentence(),
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    public function mediumPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'medium',
        ]);
    }

    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'low',
        ]);
    }

    public function roleUserConflict(): static
    {
        return $this->state(fn (array $attributes) => [
            'conflict_type' => 'role_user',
            'user_id' => User::factory(),
            'role_id' => Role::factory(),
        ]);
    }

    public function roleRoleConflict(): static
    {
        return $this->state(fn (array $attributes) => [
            'conflict_type' => 'role_role',
            'role_id' => Role::factory(),
        ]);
    }

    public function inheritanceConflict(): static
    {
        return $this->state(fn (array $attributes) => [
            'conflict_type' => 'inheritance',
            'organization_id' => Organization::factory(),
        ]);
    }

    public function explicitDenyConflict(): static
    {
        return $this->state(fn (array $attributes) => [
            'conflict_type' => 'explicit_deny',
            'user_id' => User::factory(),
        ]);
    }
}
