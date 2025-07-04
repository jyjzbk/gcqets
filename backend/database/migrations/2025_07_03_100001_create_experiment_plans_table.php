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
        Schema::create('experiment_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200)->comment('计划名称');
            $table->string('code', 50)->unique()->comment('计划编码');
            $table->unsignedBigInteger('experiment_catalog_id')->comment('实验目录ID');
            $table->unsignedBigInteger('curriculum_standard_id')->nullable()->comment('课程标准ID');
            $table->unsignedBigInteger('teacher_id')->comment('教师ID');
            $table->string('class_name', 100)->nullable()->comment('班级名称');
            $table->integer('student_count')->nullable()->comment('学生人数');
            $table->date('planned_date')->nullable()->comment('计划执行日期');
            $table->integer('planned_duration')->nullable()->comment('计划时长(分钟)');
            $table->enum('status', [
                'draft',        // 草稿
                'pending',      // 待审批
                'approved',     // 已批准
                'rejected',     // 已拒绝
                'executing',    // 执行中
                'completed',    // 已完成
                'cancelled'     // 已取消
            ])->default('draft')->comment('状态');
            $table->text('description')->nullable()->comment('计划描述');
            $table->text('objectives')->nullable()->comment('教学目标');
            $table->text('key_points')->nullable()->comment('重点难点');
            $table->text('safety_requirements')->nullable()->comment('安全要求');
            $table->json('equipment_requirements')->nullable()->comment('设备需求');
            $table->json('material_requirements')->nullable()->comment('材料需求');
            $table->text('approval_notes')->nullable()->comment('审批意见');
            $table->unsignedBigInteger('approved_by')->nullable()->comment('审批人ID');
            $table->timestamp('approved_at')->nullable()->comment('审批时间');
            $table->text('rejection_reason')->nullable()->comment('拒绝原因');
            $table->integer('revision_count')->default(0)->comment('修改次数');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->comment('优先级');
            $table->boolean('is_public')->default(false)->comment('是否公开');
            $table->json('schedule_info')->nullable()->comment('排课信息');
            $table->unsignedBigInteger('organization_id')->comment('所属组织ID');
            $table->unsignedBigInteger('created_by')->comment('创建人ID');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('更新人ID');
            $table->json('extra_data')->nullable()->comment('扩展数据');
            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index(['experiment_catalog_id', 'teacher_id']);
            $table->index(['organization_id', 'status']);
            $table->index(['planned_date', 'status']);
            $table->index(['teacher_id', 'status']);
            $table->index(['created_by', 'created_at']);
            $table->index(['approved_by', 'approved_at']);

            // 外键约束将在单独的迁移中添加
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiment_plans');
    }
};
