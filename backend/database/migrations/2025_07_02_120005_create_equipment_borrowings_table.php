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
        Schema::create('equipment_borrowings', function (Blueprint $table) {
            $table->id();
            $table->string('borrowing_code', 50)->unique()->comment('借用编号');
            $table->unsignedBigInteger('equipment_id')->comment('设备ID');
            $table->unsignedBigInteger('borrower_id')->comment('借用人ID');
            $table->unsignedBigInteger('approver_id')->nullable()->comment('审批人ID');
            $table->text('purpose')->comment('借用目的');
            $table->datetime('planned_start_time')->comment('计划开始时间');
            $table->datetime('planned_end_time')->comment('计划结束时间');
            $table->datetime('actual_start_time')->nullable()->comment('实际开始时间');
            $table->datetime('actual_end_time')->nullable()->comment('实际结束时间');
            $table->enum('status', ['pending', 'approved', 'rejected', 'borrowed', 'returned', 'overdue', 'cancelled'])->default('pending')->comment('借用状态');
            $table->text('approval_notes')->nullable()->comment('审批备注');
            $table->text('borrowing_notes')->nullable()->comment('借用备注');
            $table->text('return_notes')->nullable()->comment('归还备注');
            $table->enum('equipment_condition_before', ['good', 'normal', 'damaged'])->nullable()->comment('借用前设备状态');
            $table->enum('equipment_condition_after', ['good', 'normal', 'damaged'])->nullable()->comment('归还后设备状态');
            $table->datetime('approved_at')->nullable()->comment('审批时间');
            $table->datetime('borrowed_at')->nullable()->comment('借用时间');
            $table->datetime('returned_at')->nullable()->comment('归还时间');
            $table->unsignedBigInteger('organization_id')->comment('所属组织ID');
            $table->unsignedBigInteger('created_by')->comment('创建人ID');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('更新人ID');
            $table->json('extra_data')->nullable()->comment('扩展数据');
            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index(['equipment_id', 'status']);
            $table->index(['borrower_id', 'status']);
            $table->index(['approver_id', 'approved_at']);
            $table->index(['organization_id', 'status']);
            $table->index(['planned_start_time', 'planned_end_time']);
            $table->index(['created_by', 'created_at']);

            // 外键约束
            $table->foreign('equipment_id')->references('id')->on('equipment')->onDelete('cascade');
            $table->foreign('borrower_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_borrowings');
    }
};
