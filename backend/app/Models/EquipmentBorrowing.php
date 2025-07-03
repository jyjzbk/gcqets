<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentBorrowing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'borrowing_code',
        'equipment_id',
        'borrower_id',
        'approver_id',
        'purpose',
        'planned_start_time',
        'planned_end_time',
        'actual_start_time',
        'actual_end_time',
        'status',
        'approval_notes',
        'borrowing_notes',
        'return_notes',
        'equipment_condition_before',
        'equipment_condition_after',
        'approved_at',
        'borrowed_at',
        'returned_at',
        'organization_id',
        'created_by',
        'updated_by',
        'extra_data'
    ];

    protected $casts = [
        'extra_data' => 'array',
        'planned_start_time' => 'datetime',
        'planned_end_time' => 'datetime',
        'actual_start_time' => 'datetime',
        'actual_end_time' => 'datetime',
        'approved_at' => 'datetime',
        'borrowed_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    // 借用状态选项
    public static function getStatusOptions()
    {
        return [
            'pending' => '待审批',
            'approved' => '已批准',
            'rejected' => '已拒绝',
            'borrowed' => '已借出',
            'returned' => '已归还',
            'overdue' => '已逾期',
            'cancelled' => '已取消'
        ];
    }

    // 设备状态选项
    public static function getConditionOptions()
    {
        return [
            'good' => '良好',
            'normal' => '正常',
            'damaged' => '损坏'
        ];
    }

    // 关联关系
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function borrower()
    {
        return $this->belongsTo(User::class, 'borrower_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // 作用域
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeBorrowed($query)
    {
        return $query->where('status', 'borrowed');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->where('status', 'borrowed')
                  ->where('planned_end_time', '<', now());
            });
    }

    public function scopeByBorrower($query, $borrowerId)
    {
        return $query->where('borrower_id', $borrowerId);
    }

    public function scopeByEquipment($query, $equipmentId)
    {
        return $query->where('equipment_id', $equipmentId);
    }

    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    // 获取状态标签
    public function getStatusLabelAttribute()
    {
        $options = self::getStatusOptions();
        return $options[$this->status] ?? $this->status;
    }

    // 检查是否逾期
    public function isOverdue()
    {
        return $this->status === 'borrowed' && $this->planned_end_time < now();
    }

    // 检查是否可以审批
    public function canApprove()
    {
        return $this->status === 'pending';
    }

    // 检查是否可以借出
    public function canBorrow()
    {
        return $this->status === 'approved';
    }

    // 检查是否可以归还
    public function canReturn()
    {
        return $this->status === 'borrowed';
    }

    // 审批
    public function approve($approverId, $notes = null)
    {
        $this->update([
            'status' => 'approved',
            'approver_id' => $approverId,
            'approval_notes' => $notes,
            'approved_at' => now(),
        ]);

        return $this;
    }

    // 拒绝
    public function reject($approverId, $notes = null)
    {
        $this->update([
            'status' => 'rejected',
            'approver_id' => $approverId,
            'approval_notes' => $notes,
            'approved_at' => now(),
        ]);

        return $this;
    }

    // 借出
    public function borrow($notes = null, $condition = 'good')
    {
        $this->update([
            'status' => 'borrowed',
            'borrowing_notes' => $notes,
            'equipment_condition_before' => $condition,
            'actual_start_time' => now(),
            'borrowed_at' => now(),
        ]);

        // 更新设备状态
        $this->equipment->update(['status' => 'borrowed']);

        return $this;
    }

    // 归还
    public function returnEquipment($notes = null, $condition = 'good')
    {
        $this->update([
            'status' => 'returned',
            'return_notes' => $notes,
            'equipment_condition_after' => $condition,
            'actual_end_time' => now(),
            'returned_at' => now(),
        ]);

        // 更新设备状态
        $equipmentStatus = $condition === 'damaged' ? 'damaged' : 'available';
        $this->equipment->update(['status' => $equipmentStatus]);

        // 更新使用统计
        if ($this->actual_start_time && $this->actual_end_time) {
            $hours = $this->actual_start_time->diffInHours($this->actual_end_time);
            $this->equipment->updateUsageStats($hours);
        }

        return $this;
    }

    // 取消
    public function cancel($notes = null)
    {
        $this->update([
            'status' => 'cancelled',
            'return_notes' => $notes,
        ]);

        return $this;
    }
}
