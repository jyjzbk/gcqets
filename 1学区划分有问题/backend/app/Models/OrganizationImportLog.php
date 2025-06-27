<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationImportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'user_id',
        'parent_id',
        'total_rows',
        'success_count',
        'failed_count',
        'errors',
        'warnings',
        'status',
        'remarks'
    ];

    protected $casts = [
        'errors' => 'json',
        'warnings' => 'json',
        'total_rows' => 'integer',
        'success_count' => 'integer',
        'failed_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * 导入用户关系
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 父级组织关系
     */
    public function parentOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'parent_id');
    }

    /**
     * 获取成功率
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_rows == 0) {
            return 0;
        }
        
        return round(($this->success_count / $this->total_rows) * 100, 2);
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttribute(): string
    {
        $statusMap = [
            'processing' => '处理中',
            'completed' => '已完成',
            'failed' => '失败'
        ];

        return $statusMap[$this->status] ?? '未知';
    }
}
