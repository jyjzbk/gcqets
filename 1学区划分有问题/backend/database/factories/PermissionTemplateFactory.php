<?php

namespace Database\Factories;

use App\Models\PermissionTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionTemplateFactory extends Factory
{
    protected $model = PermissionTemplate::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->slug(2),
            'display_name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'template_type' => $this->faker->randomElement(['role', 'user', 'organization']),
            'target_level' => $this->faker->numberBetween(1, 5),
            'permission_ids' => [],
            'conditions' => null,
            'is_system' => false,
            'is_default' => false,
            'status' => 'active',
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }

    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => true,
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function roleTemplate(): static
    {
        return $this->state(fn (array $attributes) => [
            'template_type' => 'role',
        ]);
    }

    public function userTemplate(): static
    {
        return $this->state(fn (array $attributes) => [
            'template_type' => 'user',
        ]);
    }

    public function organizationTemplate(): static
    {
        return $this->state(fn (array $attributes) => [
            'template_type' => 'organization',
        ]);
    }
}
