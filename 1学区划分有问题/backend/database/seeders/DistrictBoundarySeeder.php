<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DistrictBoundary;
use App\Models\Organization;

class DistrictBoundarySeeder extends Seeder
{
    public function run()
    {
        $lianzhou = Organization::where('name', '廉州学区')->first();
        if ($lianzhou) {
            DistrictBoundary::updateOrCreate(
                ['education_district_id' => $lianzhou->id],
                [
                    'name' => '廉州学区边界',
                    'boundary_points' => json_encode([
                        ['lat' => 38.045, 'lng' => 114.840],
                        ['lat' => 38.045, 'lng' => 114.855],
                        ['lat' => 38.030, 'lng' => 114.855],
                        ['lat' => 38.030, 'lng' => 114.840],
                        ['lat' => 38.045, 'lng' => 114.840]
                    ]),
                    'school_count' => 3,
                    'total_students' => 2170,
                    'status' => 'active'
                ]
            );
        }
    }
}