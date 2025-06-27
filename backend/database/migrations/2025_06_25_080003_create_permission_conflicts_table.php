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
        if (!Schema::hasTable('permission_conflicts')) {
            Schema::create('permission_conflicts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->comment('用户ID');
                $table->unsignedBigInteger('organization_id')->nullable()->comment('组织ID');
                $table->unsignedBigInteger('permission_id')->comment('权限ID');
                $table->enum('conflict_type', ['role_permission', 'direct_permission', 'inheritance', 'template'])->comment('冲突类型');
                $table->json('conflict_sources')->comment('冲突来源JSON数组');
                $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium')->comment('严重程度');
                $table->text('description')->comment('冲突描述');
                $table->json('resolution_suggestions')->nullable()->comment('解决建议JSON数组');
                $table->enum('status', ['active', 'resolved', 'ignored'])->default('active')->comment('状态');
                $table->unsignedBigInteger('resolved_by')->nullable()->comment('解决者ID');
                $table->timestamp('resolved_at')->nullable()->comment('解决时间');
                $table->text('resolution_notes')->nullable()->comment('解决说明');
                $table->timestamp('detected_at')->useCurrent()->comment('检测时间');
                $table->timestamps();

                // 索引
                $table->index(['user_id', 'status']);
                $table->index(['organization_id', 'status']);
                $table->index(['permission_id', 'conflict_type']);
                $table->index(['conflict_type', 'severity']);
                $table->index(['status', 'detected_at']);
                $table->index(['severity', 'status']);

                // 外键约束
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
                $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
                $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_conflicts');
    }
};
