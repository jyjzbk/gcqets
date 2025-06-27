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
        Schema::create('permission_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->comment('操作用户ID');
            $table->unsignedBigInteger('target_user_id')->nullable()->comment('目标用户ID');
            $table->unsignedBigInteger('role_id')->nullable()->comment('角色ID');
            $table->unsignedBigInteger('permission_id')->nullable()->comment('权限ID');
            $table->unsignedBigInteger('organization_id')->nullable()->comment('组织机构ID');
            $table->enum('action', ['grant', 'revoke', 'modify', 'inherit', 'override'])->comment('操作类型');
            $table->enum('target_type', ['user', 'role', 'organization'])->comment('目标类型');
            $table->string('target_name')->nullable()->comment('目标名称');
            $table->json('old_values')->nullable()->comment('变更前的值');
            $table->json('new_values')->nullable()->comment('变更后的值');
            $table->text('reason')->nullable()->comment('变更原因');
            $table->string('ip_address', 45)->nullable()->comment('IP地址');
            $table->string('user_agent')->nullable()->comment('用户代理');
            $table->enum('status', ['success', 'failed', 'pending'])->default('success')->comment('操作状态');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->timestamps();

            // 外键约束
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('target_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('set null');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('set null');
            
            // 索引
            $table->index(['user_id', 'created_at']);
            $table->index(['target_user_id', 'action']);
            $table->index(['organization_id', 'action']);
            $table->index(['action', 'target_type']);
            $table->index(['created_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_audit_logs');
    }
};
