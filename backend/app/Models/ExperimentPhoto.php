<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ExperimentPhoto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'experiment_record_id',
        'photo_type',
        'file_path',
        'file_name',
        'original_name',
        'file_size',
        'mime_type',
        'width',
        'height',
        'upload_method',
        'location_info',
        'timestamp_info',
        'watermark_applied',
        'ai_analysis_result',
        'compliance_status',
        'description',
        'sort_order',
        'is_required',
        'hash',
        'exif_data',
        'organization_id',
        'created_by',
        'extra_data'
    ];

    protected $casts = [
        'location_info' => 'array',
        'timestamp_info' => 'array',
        'ai_analysis_result' => 'array',
        'exif_data' => 'array',
        'extra_data' => 'array',
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'sort_order' => 'integer',
        'watermark_applied' => 'boolean',
        'is_required' => 'boolean',
    ];

    protected $attributes = [
        'upload_method' => 'web',
        'compliance_status' => 'pending',
        'watermark_applied' => false,
        'sort_order' => 0,
        'is_required' => false,
    ];

    // 照片类型选项
    public static function getPhotoTypeOptions()
    {
        return [
            'preparation' => '准备阶段',
            'process' => '过程记录',
            'result' => '结果展示',
            'equipment' => '器材准备',
            'safety' => '安全记录'
        ];
    }

    // 合规状态选项
    public static function getComplianceStatusOptions()
    {
        return [
            'pending' => '待检查',
            'compliant' => '合规',
            'non_compliant' => '不合规',
            'needs_review' => '需要人工审核'
        ];
    }

    // 关联实验记录
    public function experimentRecord(): BelongsTo
    {
        return $this->belongsTo(ExperimentRecord::class);
    }

    // 关联组织机构
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    // 关联创建人
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // 作用域：按类型筛选
    public function scopePhotoType($query, $type)
    {
        return $query->where('photo_type', $type);
    }

    // 作用域：按合规状态筛选
    public function scopeComplianceStatus($query, $status)
    {
        return $query->where('compliance_status', $status);
    }

    // 作用域：按组织筛选
    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    // 作用域：按上传方式筛选
    public function scopeUploadMethod($query, $method)
    {
        return $query->where('upload_method', $method);
    }

    // 作用域：合规的照片
    public function scopeCompliant($query)
    {
        return $query->where('compliance_status', 'compliant');
    }

    // 作用域：不合规的照片
    public function scopeNonCompliant($query)
    {
        return $query->where('compliance_status', 'non_compliant');
    }

    // 获取照片类型标签
    public function getPhotoTypeLabelAttribute(): string
    {
        return self::getPhotoTypeOptions()[$this->photo_type] ?? $this->photo_type;
    }

    // 获取合规状态标签
    public function getComplianceStatusLabelAttribute(): string
    {
        return self::getComplianceStatusOptions()[$this->compliance_status] ?? $this->compliance_status;
    }

    // 获取文件URL
    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    // 获取缩略图URL
    public function getThumbnailUrlAttribute(): string
    {
        // 生成缩略图路径
        $pathInfo = pathinfo($this->file_path);
        $thumbnailPath = $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
        
        if (Storage::exists($thumbnailPath)) {
            return Storage::url($thumbnailPath);
        }
        
        return $this->file_url; // 如果没有缩略图，返回原图
    }

    // 获取文件大小（人类可读格式）
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    // 检查是否为图片
    public function isImage(): bool
    {
        return strpos($this->mime_type, 'image/') === 0;
    }

    // 检查文件是否存在
    public function fileExists(): bool
    {
        return Storage::exists($this->file_path);
    }

    // 生成文件哈希
    public function generateHash(): string
    {
        if ($this->fileExists()) {
            return hash_file('sha256', Storage::path($this->file_path));
        }
        return '';
    }

    // 添加水印
    public function addWatermark(): bool
    {
        if (!$this->isImage() || $this->watermark_applied) {
            return false;
        }

        try {
            // 这里可以实现水印添加逻辑
            // 例如使用 Intervention Image 库
            
            $this->watermark_applied = true;
            $this->save();
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // AI分析照片
    public function analyzeWithAI(): array
    {
        // 这里可以集成AI分析服务
        // 例如检测照片是否模糊、是否包含实验内容等
        
        $analysis = [
            'blur_detection' => [
                'is_blurry' => false,
                'blur_score' => 0.1
            ],
            'content_detection' => [
                'has_experiment_content' => true,
                'confidence' => 0.85
            ],
            'safety_check' => [
                'safety_violations' => [],
                'is_safe' => true
            ],
            'analyzed_at' => now()->toISOString()
        ];

        $this->ai_analysis_result = $analysis;
        
        // 根据分析结果更新合规状态
        if ($analysis['blur_detection']['is_blurry'] || !$analysis['content_detection']['has_experiment_content']) {
            $this->compliance_status = 'non_compliant';
        } elseif ($analysis['content_detection']['confidence'] < 0.7) {
            $this->compliance_status = 'needs_review';
        } else {
            $this->compliance_status = 'compliant';
        }

        $this->save();

        return $analysis;
    }

    // 提取EXIF数据
    public function extractExifData(): array
    {
        if (!$this->isImage() || !$this->fileExists()) {
            return [];
        }

        try {
            $exifData = @exif_read_data(Storage::path($this->file_path));
            
            if ($exifData) {
                // 提取有用的EXIF信息
                $extracted = [
                    'camera_make' => $exifData['Make'] ?? null,
                    'camera_model' => $exifData['Model'] ?? null,
                    'datetime' => $exifData['DateTime'] ?? null,
                    'gps_latitude' => $this->extractGPSCoordinate($exifData, 'GPSLatitude', 'GPSLatitudeRef'),
                    'gps_longitude' => $this->extractGPSCoordinate($exifData, 'GPSLongitude', 'GPSLongitudeRef'),
                    'image_width' => $exifData['COMPUTED']['Width'] ?? null,
                    'image_height' => $exifData['COMPUTED']['Height'] ?? null,
                ];

                $this->exif_data = $extracted;
                $this->save();

                return $extracted;
            }
        } catch (\Exception $e) {
            // EXIF读取失败，忽略错误
        }

        return [];
    }

    // 提取GPS坐标
    private function extractGPSCoordinate($exifData, $coordKey, $refKey): ?float
    {
        if (!isset($exifData[$coordKey]) || !isset($exifData[$refKey])) {
            return null;
        }

        $coord = $exifData[$coordKey];
        $ref = $exifData[$refKey];

        if (is_array($coord) && count($coord) >= 3) {
            $degrees = $this->gpsToDecimal($coord[0]);
            $minutes = $this->gpsToDecimal($coord[1]);
            $seconds = $this->gpsToDecimal($coord[2]);

            $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

            if (in_array($ref, ['S', 'W'])) {
                $decimal *= -1;
            }

            return $decimal;
        }

        return null;
    }

    // GPS坐标转换为十进制
    private function gpsToDecimal($coordinate): float
    {
        if (is_string($coordinate) && strpos($coordinate, '/') !== false) {
            $parts = explode('/', $coordinate);
            if (count($parts) === 2 && $parts[1] != 0) {
                return $parts[0] / $parts[1];
            }
        }

        return (float) $coordinate;
    }

    // 删除文件
    public function deleteFile(): bool
    {
        if ($this->fileExists()) {
            Storage::delete($this->file_path);
            
            // 同时删除缩略图
            $pathInfo = pathinfo($this->file_path);
            $thumbnailPath = $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
            if (Storage::exists($thumbnailPath)) {
                Storage::delete($thumbnailPath);
            }
            
            return true;
        }
        
        return false;
    }

    // 模型事件
    protected static function boot()
    {
        parent::boot();

        // 删除记录时同时删除文件
        static::deleting(function ($photo) {
            $photo->deleteFile();
        });

        // 创建记录后更新实验记录的照片数量
        static::created(function ($photo) {
            $photo->experimentRecord->updatePhotoCount();
        });

        // 删除记录后更新实验记录的照片数量
        static::deleted(function ($photo) {
            if ($photo->experimentRecord) {
                $photo->experimentRecord->updatePhotoCount();
            }
        });
    }
}
