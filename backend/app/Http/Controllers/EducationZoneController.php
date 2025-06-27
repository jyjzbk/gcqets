<?php

namespace App\Http\Controllers;

use App\Models\EducationZone;
use App\Models\Organization;
use App\Models\ZoneSchool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EducationZoneController extends Controller
{
    /**
     * Display a listing of the education zones.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = EducationZone::with(['district', 'manager']);

        // Filter by district
        if ($request->has('district_id')) {
            $query->where('district_id', $request->input('district_id'));
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Search by name or code
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        $educationZones = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $educationZones
        ]);
    }

    /**
     * Store a newly created education zone in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:education_zones',
                'district_id' => 'required|exists:organizations,id',
                'description' => 'nullable|string',
                'boundary_points' => 'nullable|string',
                'center_longitude' => 'nullable|numeric|between:-180,180',
                'center_latitude' => 'nullable|numeric|between:-90,90',
                'area' => 'nullable|numeric|min:0',
                'student_capacity' => 'nullable|integer|min:0',
                'manager_id' => 'nullable|exists:users,id',
                'manager_name' => 'nullable|string|max:255',
                'manager_phone' => 'nullable|string|max:20',
                'status' => 'nullable|in:active,inactive,planning'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if district exists and is a district type
            $district = Organization::findOrFail($request->input('district_id'));
            if (!$district->isDistrict()) {
                return response()->json([
                    'success' => false,
                    'message' => '所选组织不是区县类型'
                ], 422);
            }

            $educationZone = EducationZone::create($request->all());

            // If boundary points are provided, calculate center if not provided
            if ($request->filled('boundary_points') && (!$request->filled('center_longitude') || !$request->filled('center_latitude'))) {
                $educationZone->calculateCenter()->save();
            }

            return response()->json([
                'success' => true,
                'message' => '学区创建成功',
                'data' => $educationZone
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '验证失败',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '学区创建失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified education zone.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $educationZone = EducationZone::with(['district', 'manager', 'schools'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $educationZone
        ]);
    }

    /**
     * Update the specified education zone in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $educationZone = EducationZone::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'code' => 'sometimes|required|string|max:50|unique:education_zones,code,' . $id,
                'district_id' => 'sometimes|required|exists:organizations,id',
                'description' => 'nullable|string',
                'boundary_points' => 'nullable|string',
                'center_longitude' => 'nullable|numeric|between:-180,180',
                'center_latitude' => 'nullable|numeric|between:-90,90',
                'area' => 'nullable|numeric|min:0',
                'student_capacity' => 'nullable|integer|min:0',
                'manager_id' => 'nullable|exists:users,id',
                'manager_name' => 'nullable|string|max:255',
                'manager_phone' => 'nullable|string|max:20',
                'status' => 'nullable|in:active,inactive,planning'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if district exists and is a district type
            if ($request->has('district_id')) {
                $district = Organization::findOrFail($request->input('district_id'));
                if (!$district->isDistrict()) {
                    return response()->json([
                        'success' => false,
                        'message' => '所选组织不是区县类型'
                    ], 422);
                }
            }

            $educationZone->update($request->all());

            // If boundary points are updated, recalculate center if not provided
            if ($request->filled('boundary_points') && (!$request->filled('center_longitude') || !$request->filled('center_latitude'))) {
                $educationZone->calculateCenter()->save();
            }

            // Update school count and student count
            $educationZone->updateSchoolCount()->updateCurrentStudents()->save();

            return response()->json([
                'success' => true,
                'message' => '学区更新成功',
                'data' => $educationZone
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '验证失败',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '学区更新失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified education zone from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $educationZone = EducationZone::findOrFail($id);

            // Check if there are schools assigned to this zone
            if ($educationZone->schools()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => '无法删除已分配学校的学区'
                ], 422);
            }

            $educationZone->delete();

            return response()->json([
                'success' => true,
                'message' => '学区删除成功'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '学区删除失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get schools in the specified education zone.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getSchools($id)
    {
        $educationZone = EducationZone::findOrFail($id);
        $schools = $educationZone->schools()->with('zoneSchools')->get();

        return response()->json([
            'success' => true,
            'data' => $schools
        ]);
    }

    /**
     * Assign schools to the education zone.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function assignSchools(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'school_ids' => 'required|array',
                'school_ids.*' => 'exists:organizations,id',
                'assignment_type' => 'required|in:auto,manual',
                'assignment_reason' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $educationZone = EducationZone::findOrFail($id);
            $schoolIds = $request->input('school_ids');
            $assignmentType = $request->input('assignment_type');
            $assignmentReason = $request->input('assignment_reason');

            // Begin transaction
            DB::beginTransaction();

            $assignedCount = 0;
            $errors = [];

            foreach ($schoolIds as $schoolId) {
                try {
                    $school = Organization::findOrFail($schoolId);

                    // Check if school is actually a school
                    if (!$school->isSchool()) {
                        $errors[] = [
                            'school_id' => $schoolId,
                            'message' => '组织不是学校类型'
                        ];
                        continue;
                    }

                    // Check if school is already assigned to this zone
                    if ($educationZone->schools()->where('organizations.id', $schoolId)->exists()) {
                        $errors[] = [
                            'school_id' => $schoolId,
                            'message' => '学校已分配到该学区'
                        ];
                        continue;
                    }

                    // Create zone-school relationship
                    $zoneSchool = new ZoneSchool([
                        'zone_id' => $id,
                        'school_id' => $schoolId,
                        'assignment_type' => $assignmentType,
                        'assignment_reason' => $assignmentReason,
                        'assigned_by' => Auth::id(),
                        'assigned_at' => now()
                    ]);

                    // Calculate distance if school has coordinates
                    if ($school->latitude && $school->longitude && $educationZone->center_latitude && $educationZone->center_longitude) {
                        $zoneSchool->calculateDistance();
                    }

                    $zoneSchool->save();
                    $assignedCount++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'school_id' => $schoolId,
                        'message' => $e->getMessage()
                    ];
                }
            }

            // Update school count and student count
            $educationZone->updateSchoolCount()->updateCurrentStudents()->save();

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "成功分配 {$assignedCount} 所学校到学区",
                'data' => [
                    'assigned_count' => $assignedCount,
                    'errors' => $errors
                ]
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '验证失败',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '学校分配失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove schools from the education zone.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function removeSchools(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'school_ids' => 'required|array',
                'school_ids.*' => 'exists:organizations,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $educationZone = EducationZone::findOrFail($id);
            $schoolIds = $request->input('school_ids');

            // Begin transaction
            DB::beginTransaction();

            // Remove schools from zone
            $count = $educationZone->schools()->detach($schoolIds);

            // Update school count and student count
            $educationZone->updateSchoolCount()->updateCurrentStudents()->save();

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "成功从学区移除 {$count} 所学校",
                'data' => [
                    'removed_count' => $count
                ]
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '验证失败',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '学校移除失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-assign schools to education zones based on distance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function autoAssignSchools(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'district_id' => 'required|exists:organizations,id',
                'max_distance' => 'nullable|numeric|min:0',
                'override_existing' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $districtId = $request->input('district_id');
            $maxDistance = $request->input('max_distance', 5); // Default 5km
            $overrideExisting = $request->input('override_existing', false);

            // Check if district exists and is a district type
            $district = Organization::findOrFail($districtId);
            if (!$district->isDistrict()) {
                return response()->json([
                    'success' => false,
                    'message' => '所选组织不是区县类型'
                ], 422);
            }

            // Get all education zones in the district
            $educationZones = EducationZone::where('district_id', $districtId)
                ->where('status', 'active')
                ->get();

            if ($educationZones->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => '所选区县没有活跃的学区'
                ], 422);
            }

            // Get all schools in the district
            $schools = Organization::where('parent_id', $districtId)
                ->schools()
                ->active()
                ->get();

            if ($schools->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => '所选区县没有学校'
                ], 422);
            }

            // Begin transaction
            DB::beginTransaction();

            $assignedCount = 0;
            $unassignedCount = 0;
            $errors = [];

            foreach ($schools as $school) {
                try {
                    // Skip schools without coordinates
                    if (!$school->latitude || !$school->longitude) {
                        $errors[] = [
                            'school_id' => $school->id,
                            'message' => '学校没有地理坐标'
                        ];
                        $unassignedCount++;
                        continue;
                    }

                    // Skip schools already assigned to a zone if not overriding
                    if (!$overrideExisting && ZoneSchool::where('school_id', $school->id)->exists()) {
                        $errors[] = [
                            'school_id' => $school->id,
                            'message' => '学校已分配到学区'
                        ];
                        continue;
                    }

                    // Find the closest education zone
                    $closestZone = null;
                    $minDistance = PHP_FLOAT_MAX;

                    foreach ($educationZones as $zone) {
                        // Skip zones without coordinates
                        if (!$zone->center_latitude || !$zone->center_longitude) {
                            continue;
                        }

                        // Calculate distance
                        $distance = $this->calculateDistance(
                            $school->latitude,
                            $school->longitude,
                            $zone->center_latitude,
                            $zone->center_longitude
                        );

                        if ($distance < $minDistance) {
                            $minDistance = $distance;
                            $closestZone = $zone;
                        }
                    }

                    // If a zone is found and within max distance
                    if ($closestZone && $minDistance <= $maxDistance) {
                        // Remove existing assignments if overriding
                        if ($overrideExisting) {
                            ZoneSchool::where('school_id', $school->id)->delete();
                        }

                        // Create zone-school relationship
                        $zoneSchool = new ZoneSchool([
                            'zone_id' => $closestZone->id,
                            'school_id' => $school->id,
                            'assignment_type' => 'auto',
                            'distance' => $minDistance,
                            'assignment_reason' => "自动分配 - 距离: {$minDistance}公里",
                            'assigned_by' => Auth::id(),
                            'assigned_at' => now()
                        ]);

                        $zoneSchool->save();
                        $assignedCount++;
                    } else {
                        $errors[] = [
                            'school_id' => $school->id,
                            'message' => '没有找到合适的学区或距离超出限制'
                        ];
                        $unassignedCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = [
                        'school_id' => $school->id,
                        'message' => $e->getMessage()
                    ];
                    $unassignedCount++;
                }
            }

            // Update school count and student count for all zones
            foreach ($educationZones as $zone) {
                $zone->updateSchoolCount()->updateCurrentStudents()->save();
            }

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "自动分配完成: {$assignedCount} 所学校已分配, {$unassignedCount} 所未分配",
                'data' => [
                    'assigned_count' => $assignedCount,
                    'unassigned_count' => $unassignedCount,
                    'errors' => $errors
                ]
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '验证失败',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '自动分配失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate distance between two points using Haversine formula.
     *
     * @param  float  $lat1
     * @param  float  $lon1
     * @param  float  $lat2
     * @param  float  $lon2
     * @return float
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Radius of the Earth in kilometers
        
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);
        
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        $distance = $angle * $earthRadius;
        
        return round($distance, 2);
    }
}
