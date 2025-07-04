<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 添加性能优化索引
     */
    public function up()
    {
        // 实验计划表索引优化
        Schema::table('experiment_plans', function (Blueprint $table) {
            // 状态和日期组合索引（用于日历和监控查询）
            $table->index(['status', 'planned_date'], 'idx_status_planned_date');
            
            // 组织和创建时间组合索引（用于权限过滤和时间范围查询）
            $table->index(['organization_id', 'created_at'], 'idx_org_created');
            
            // 教师和状态组合索引（用于教师相关查询）
            $table->index(['teacher_id', 'status'], 'idx_teacher_status');
            
            // 实验目录索引（用于统计分析）
            $table->index('experiment_catalog_id', 'idx_catalog');
        });

        // 实验记录表索引优化
        Schema::table('experiment_records', function (Blueprint $table) {
            // 计划和状态组合索引
            $table->index(['experiment_plan_id', 'completion_status'], 'idx_plan_completion');
            
            // 审核状态和创建时间组合索引（用于审核列表）
            $table->index(['review_status', 'created_at'], 'idx_review_created');
            
            // 执行日期索引（用于日历查询）
            $table->index('execution_date', 'idx_execution_date');
        });

        // 实验照片表索引优化
        Schema::table('experiment_photos', function (Blueprint $table) {
            // 记录和类型组合索引
            $table->index(['experiment_record_id', 'photo_type'], 'idx_record_type');
            
            // 创建时间索引（用于时间范围查询）
            $table->index('created_at', 'idx_photo_created');
        });

        // 审核日志表索引优化
        Schema::table('experiment_review_logs', function (Blueprint $table) {
            // 记录和审核类型组合索引
            $table->index(['experiment_record_id', 'review_type'], 'idx_record_review_type');
            
            // 审核人和时间组合索引（用于审核统计）
            $table->index(['reviewer_id', 'created_at'], 'idx_reviewer_created');
            
            // 组织和时间组合索引（用于权限过滤）
            $table->index(['organization_id', 'created_at'], 'idx_org_review_created');
        });
    }

    /**
     * 回滚索引
     */
    public function down()
    {
        Schema::table('experiment_plans', function (Blueprint $table) {
            $table->dropIndex('idx_status_planned_date');
            $table->dropIndex('idx_org_created');
            $table->dropIndex('idx_teacher_status');
            $table->dropIndex('idx_catalog');
        });

        Schema::table('experiment_records', function (Blueprint $table) {
            $table->dropIndex('idx_plan_completion');
            $table->dropIndex('idx_review_created');
            $table->dropIndex('idx_execution_date');
        });

        Schema::table('experiment_photos', function (Blueprint $table) {
            $table->dropIndex('idx_record_type');
            $table->dropIndex('idx_photo_created');
        });

        Schema::table('experiment_review_logs', function (Blueprint $table) {
            $table->dropIndex('idx_record_review_type');
            $table->dropIndex('idx_reviewer_created');
            $table->dropIndex('idx_org_review_created');
        });
    }
};
