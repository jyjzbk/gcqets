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
        Schema::create('experiment_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('experiment_plan_id')->comment('实验计划ID');
            $table->date('execution_date')->comment('执行日期');
            $table->time('start_time')->nullable()->comment('开始时间');
            $table->time('end_time')->nullable()->comment('结束时间');
            $table->integer('actual_duration')->nullable()->comment('实际时长(分钟)');
            $table->integer('actual_student_count')->nullable()->comment('实际参与学生数');
            $table->enum('completion_status', [
                'not_started',  // 未开始
                'in_progress',  // 进行中
                'partial',      // 部分完成
                'completed',    // 已完成
                'cancelled'     // 已取消
            ])->default('not_started')->comment('完成状态');
            $table->text('execution_notes')->nullable()->comment('执行说明');
            $table->text('problems_encountered')->nullable()->comment('遇到的问题');
            $table->text('solutions_applied')->nullable()->comment('解决方案');
            $table->text('teaching_reflection')->nullable()->comment('教学反思');
            $table->text('student_feedback')->nullable()->comment('学生反馈');
            $table->json('equipment_used')->nullable()->comment('使用的设备');
            $table->json('materials_consumed')->nullable()->comment('消耗的材料');
            $table->json('data_records')->nullable()->comment('实验数据记录');
            $table->text('safety_incidents')->nullable()->comment('安全事件记录');
            $table->enum('status', [
                'draft',            // 草稿
                'submitted',        // 已提交
                'under_review',     // 审核中
                'approved',         // 已通过
                'rejected',         // 已拒绝
                'revision_required' // 需要修改
            ])->default('draft')->comment('审核状态');
            $table->text('review_notes')->nullable()->comment('审核意见');
            $table->unsignedBigInteger('reviewed_by')->nullable()->comment('审核人ID');
            $table->timestamp('reviewed_at')->nullable()->comment('审核时间');
            $table->integer('photo_count')->default(0)->comment('照片数量');
            $table->boolean('equipment_confirmed')->default(false)->comment('器材准备已确认');
            $table->timestamp('equipment_confirmed_at')->nullable()->comment('器材确认时间');
            $table->json('validation_results')->nullable()->comment('验证结果');
            $table->decimal('completion_percentage', 5, 2)->default(0)->comment('完成百分比');
            $table->unsignedBigInteger('organization_id')->comment('所属组织ID');
            $table->unsignedBigInteger('created_by')->comment('创建人ID');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('更新人ID');
            $table->json('extra_data')->nullable()->comment('扩展数据');
            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index(['experiment_plan_id', 'execution_date']);
            $table->index(['organization_id', 'status']);
            $table->index(['completion_status', 'status']);
            $table->index(['created_by', 'created_at']);
            $table->index(['reviewed_by', 'reviewed_at']);
            $table->index(['execution_date', 'completion_status']);

            // 外键约束将在单独的迁移中添加
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiment_records');
    }
};
