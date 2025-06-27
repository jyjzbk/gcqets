<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\SchoolLocation;

class SchoolLocationSeeder extends Seeder
{
    public function run()
    {
        // 东城小学
        $dongcheng = Organization::where('name', '东城小学')->first();
        if ($dongcheng) {
            SchoolLocation::updateOrCreate(
                ['organization_id' => $dongcheng->id],
                [
                    'latitude' => 38.0425,
                    'longitude' => 114.8469,
                    'address' => '河北省石家庄市藁城区廉州镇东城村',
                    'student_count' => 800,
                    'teacher_count' => 45,
                    'class_count' => 20,
                    'area_size' => 2.5,
                    'school_type' => 'primary'
                ]
            );
        }

        // 西城小学
        $xicheng = Organization::where('name', '西城小学')->first();
        if ($xicheng) {
            SchoolLocation::updateOrCreate(
                ['organization_id' => $xicheng->id],
                [
                    'latitude' => 38.0387,
                    'longitude' => 114.8321,
                    'address' => '河北省石家庄市藁城区廉州镇西城村',
                    'student_count' => 650,
                    'teacher_count' => 38,
                    'class_count' => 18,
                    'area_size' => 2.2,
                    'school_type' => 'primary'
                ]
            );
        }

        // 南城小学
        $nancheng = Organization::where('name', '南城小学')->first();
        if ($nancheng) {
            SchoolLocation::updateOrCreate(
                ['organization_id' => $nancheng->id],
                [
                    'latitude' => 38.0352,
                    'longitude' => 114.8483,
                    'address' => '河北省石家庄市藁城区廉州镇南城村',
                    'student_count' => 720,
                    'teacher_count' => 42,
                    'class_count' => 19,
                    'area_size' => 2.3,
                    'school_type' => 'primary'
                ]
            );
        }
    }
}