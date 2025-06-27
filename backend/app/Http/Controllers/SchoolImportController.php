<?php

namespace App\Http\Controllers;

use App\Models\ImportLog;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SchoolImportController extends Controller
{
    /**
     * Display the import form.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $importLogs = ImportLog::where('import_type', 'school')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'import_logs' => $importLogs
            ]
        ]);
    }

    /**
     * Upload Excel file for import.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
                'parent_id' => 'required|exists:organizations,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('imports', $fileName, 'public');

            // Create import log
            $importLog = ImportLog::create([
                'user_id' => Auth::id(),
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'import_type' => 'school',
                'status' => 'pending',
                'import_options' => [
                    'parent_id' => $request->input('parent_id')
                ]
            ]);

            // Queue the import job
            // In a real implementation, you would dispatch a job here
            // For now, we'll process it immediately
            $this->processImport($importLog->id);

            return response()->json([
                'success' => true,
                'message' => '文件上传成功，正在处理导入',
                'data' => [
                    'import_log_id' => $importLog->id
                ]
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
                'message' => '文件上传失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process the import.
     *
     * @param  int  $importLogId
     * @return void
     */
    public function processImport($importLogId)
    {
        $importLog = ImportLog::findOrFail($importLogId);
        $importLog->update([
            'status' => 'processing',
            'started_at' => now()
        ]);

        try {
            // In a real implementation, you would use a package like PhpSpreadsheet
            // to read the Excel file and process the data
            // For now, we'll simulate the process

            // Get the file path
            $filePath = Storage::disk('public')->path($importLog->file_path);

            // Read the file (simulated)
            $data = $this->readExcelFile($filePath);

            // Process the data
            $parentId = $importLog->import_options['parent_id'] ?? null;
            $results = $this->processSchoolData($data, $parentId);

            // Update the import log
            $importLog->update([
                'status' => 'completed',
                'total_rows' => $results['total'],
                'processed_rows' => $results['processed'],
                'success_rows' => $results['success'],
                'failed_rows' => $results['failed'],
                'error_details' => $results['errors'],
                'completed_at' => now()
            ]);
        } catch (\Exception $e) {
            // Update the import log with error
            $importLog->update([
                'status' => 'failed',
                'error_details' => [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ],
                'completed_at' => now()
            ]);
        }
    }

    /**
     * Get import status.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function status($id)
    {
        $importLog = ImportLog::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'import_log' => $importLog,
                'progress' => $importLog->progress_percentage
            ]
        ]);
    }

    /**
     * Download import template.
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadTemplate()
    {
        // In a real implementation, you would create and return an Excel template
        // For now, we'll return a simple response
        return response()->json([
            'success' => true,
            'message' => '模板下载功能尚未实现'
        ]);
    }

    /**
     * Read Excel file (simulated).
     *
     * @param  string  $filePath
     * @return array
     */
    private function readExcelFile($filePath)
    {
        // In a real implementation, you would use a package like PhpSpreadsheet
        // to read the Excel file and return the data
        // For now, we'll return a simulated data array
        return [
            [
                'name' => '示范小学',
                'code' => 'XX001',
                'address' => '示范路123号',
                'contact_person' => '张校长',
                'contact_phone' => '13800138000',
                'student_count' => 1200,
                'campus_area' => 15000,
                'principal_name' => '张校长',
                'principal_phone' => '13800138000',
                'principal_email' => 'principal@example.com',
                'founded_year' => 1990,
                'school_type' => 'public',
                'education_level' => 'primary',
                'longitude' => 116.404,
                'latitude' => 39.915
            ],
            [
                'name' => '示范中学',
                'code' => 'XX002',
                'address' => '示范路456号',
                'contact_person' => '李校长',
                'contact_phone' => '13900139000',
                'student_count' => 1800,
                'campus_area' => 25000,
                'principal_name' => '李校长',
                'principal_phone' => '13900139000',
                'principal_email' => 'principal2@example.com',
                'founded_year' => 1985,
                'school_type' => 'public',
                'education_level' => 'middle',
                'longitude' => 116.415,
                'latitude' => 39.925
            ]
        ];
    }

    /**
     * Process school data (simulated).
     *
     * @param  array  $data
     * @param  int  $parentId
     * @return array
     */
    private function processSchoolData($data, $parentId)
    {
        $total = count($data);
        $processed = 0;
        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            $processed++;

            try {
                // Validate the row data
                $validator = Validator::make($row, [
                    'name' => 'required|string|max:100',
                    'code' => 'required|string|max:50|unique:organizations,code',
                    'address' => 'nullable|string|max:200',
                    'contact_person' => 'nullable|string|max:50',
                    'contact_phone' => 'nullable|string|max:20',
                    'student_count' => 'nullable|integer',
                    'campus_area' => 'nullable|numeric',
                    'principal_name' => 'nullable|string|max:50',
                    'principal_phone' => 'nullable|string|max:20',
                    'principal_email' => 'nullable|email|max:100',
                    'founded_year' => 'nullable|integer|min:1800|max:' . date('Y'),
                    'school_type' => 'nullable|in:public,private,other',
                    'education_level' => 'nullable|in:primary,middle,high,vocational,comprehensive',
                    'longitude' => 'nullable|numeric|between:-180,180',
                    'latitude' => 'nullable|numeric|between:-90,90'
                ]);

                if ($validator->fails()) {
                    throw new ValidationException($validator);
                }

                // Create the organization
                $parent = Organization::findOrFail($parentId);
                
                $organization = Organization::create([
                    'name' => $row['name'],
                    'code' => $row['code'],
                    'type' => 'school',
                    'level' => $parent->level + 1,
                    'parent_id' => $parentId,
                    'description' => $row['description'] ?? null,
                    'contact_person' => $row['contact_person'] ?? null,
                    'contact_phone' => $row['contact_phone'] ?? null,
                    'address' => $row['address'] ?? null,
                    'longitude' => $row['longitude'] ?? null,
                    'latitude' => $row['latitude'] ?? null,
                    'status' => 'active',
                    'student_count' => $row['student_count'] ?? null,
                    'campus_area' => $row['campus_area'] ?? null,
                    'principal_name' => $row['principal_name'] ?? null,
                    'principal_phone' => $row['principal_phone'] ?? null,
                    'principal_email' => $row['principal_email'] ?? null,
                    'founded_year' => $row['founded_year'] ?? null,
                    'school_type' => $row['school_type'] ?? null,
                    'education_level' => $row['education_level'] ?? null
                ]);

                $success++;
            } catch (ValidationException $e) {
                $failed++;
                $errors[] = [
                    'row' => $index + 1,
                    'data' => $row,
                    'errors' => $e->errors()
                ];
            } catch (\Exception $e) {
                $failed++;
                $errors[] = [
                    'row' => $index + 1,
                    'data' => $row,
                    'message' => $e->getMessage()
                ];
            }
        }

        return [
            'total' => $total,
            'processed' => $processed,
            'success' => $success,
            'failed' => $failed,
            'errors' => $errors
        ];
    }
}
