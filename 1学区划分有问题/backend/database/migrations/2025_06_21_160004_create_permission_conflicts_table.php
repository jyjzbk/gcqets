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
        Schema::create('permission_conflicts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->comment('用户ID');
            $table->unsignedBigInteger('role_id')->nullable()->comment('角色ID');
            $table->unsignedBigInteger('organization_id')->nullable()->comment('组织机构ID');
            $table->unsignedBigInteger('permission_id')->comment('权限ID');
            $table->enum('conflict_type', ['role_user', 'role_role', 'inheritance', 'explicit_deny'])->comment('冲突类型');
            $table->json('conflict_sources')->comment('冲突来源详情');
            $table->enum('resolution_strategy', ['allow', 'deny', 'manual'])->default('manual')->comment('解决策略');
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium')->comment('优先级');
            $table->enum('status', ['unresolved', 'resolved', 'ignored'])->default('unresolved')->comment('状态');
            $table->unsignedBigInteger('resolved_by')->nullable()->comment('解决者ID');
            $table->timestamp('resolved_at')->nullable()->comment('解决时间');
            $table->text('resolution_notes')->nullable()->comment('解决说明');
            $table->timestamps();

            // 外键约束
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
            
            // 索引
            $table->index(['user_id', 'status']);
            $table->index(['role_id', 'conflict_type']);
            $table->index(['organization_id', 'priority']);
            $table->index(['permission_id', 'status']);
            $table->index(['conflict_type', 'status']);
            $table->index(['priority', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_conflicts');
    }
};
