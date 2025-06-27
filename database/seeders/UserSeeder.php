<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // 创建超级管理员
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'organization_id' => Organization::where('type', 'province')->first()->id
        ]);
        $superAdmin->roles()->attach(Role::where('name', 'super_admin')->first());

        // 创建省级管理员
        $provinceAdmin = User::create([
            'name' => 'Province Admin',
            'email' => 'province@example.com',
            'password' => Hash::make('password'),
            'organization_id' => Organization::where('type', 'province')->first()->id
        ]);
        $provinceAdmin->roles()->attach(Role::where('name', 'province_admin')->first());

        // 创建市级管理员
        $cityAdmin = User::create([
            'name' => 'City Admin',
            'email' => 'city@example.com',
            'password' => Hash::make('password'),
            'organization_id' => Organization::where('type', 'city')->first()->id
        ]);
        $cityAdmin->roles()->attach(Role::where('name', 'city_admin')->first());

        // 创建学校管理员
        $schoolAdmin = User::create([
            'name' => 'School Admin',
            'email' => 'school@example.com',
            'password' => Hash::make('password'),
            'organization_id' => Organization::where('type', 'school')->first()->id
        ]);
        $schoolAdmin->roles()->attach(Role::where('name', 'school_admin')->first());
    }
}