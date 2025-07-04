<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('experiment_review_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('experiment_record_id')->comment('实验记录ID');
            $table->enum('review_type', [
                'submit',           // 提交审核
                'approve',          // 审核通过
                'reject',           // 审核拒绝
                'revision_request', // 要求修改
                'force_complete',   // 强制完成
                'batch_approve',    // 批量通过
                'batch_reject',     // 批量拒绝
                'ai_check',         // AI检查
                'manual_check'      // 人工检查
            ])->comment('审核类型');
            $table->string('previous_status', 50)->nullable()->comment('之前状态');
            $table->string('new_status', 50)->comment('新状态');
            $table->text('review_notes')->nullable()->comment('审核意见');
            $table->json('review_details')->nullable()->comment('审核详情');
            $table->json('attachment_files')->nullable()->comment('附件文件');
            $table->enum('review_category', [
                'format',           // 格式问题
                'content',          // 内容问题
                'photo',            // 照片问题
                'data',             // 数据问题
                'safety',           // 安全问题
                'completeness',     // 完整性问题
                'other'             // 其他问题
            ])->nullable()->comment('审核分类');
            $table->integer('review_score')->nullable()->comment('审核评分(1-10)');
            $table->boolean('is_ai_review')->default(false)->comment('是否AI审核');
            $table->json('ai_analysis_result')->nullable()->comment('AI分析结果');
            $table->unsignedBigInteger('reviewer_id')->comment('审核人ID');
            $table->string('reviewer_role', 50)->comment('审核人角色');
            $table->string('reviewer_name', 100)->comment('审核人姓名');
            $table->timestamp('review_deadline')->nullable()->comment('审核截止时间');
            $table->integer('review_duration')->nullable()->comment('审核耗时(分钟)');
            $table->boolean('is_urgent')->default(false)->comment('是否紧急');
            $table->string('ip_address', 45)->nullable()->comment('操作IP地址');
            $table->string('user_agent', 500)->nullable()->comment('用户代理');
            $table->unsignedBigInteger('organization_id')->comment('所属组织ID');
            $table->json('extra_data')->nullable()->comment('扩展数据');
            $table->timestamps();

            // 索引
            $table->index(['experiment_record_id', 'review_type']);
            $table->index(['reviewer_id', 'created_at']);
            $table->index(['organization_id', 'review_type']);
            $table->index(['new_status', 'created_at']);
            $table->index(['review_category', 'created_at']);
            $table->index(['is_ai_review', 'created_at']);
            $table->index(['is_urgent', 'created_at']);

            // 外键约束将在单独的迁移中添加
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiment_review_logs');
    }
};
