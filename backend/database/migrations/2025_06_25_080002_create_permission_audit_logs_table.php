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
        if (!Schema::hasTable('permission_audit_logs')) {
            Schema::create('permission_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->comment('操作用户ID');
                $table->unsignedBigInteger('target_user_id')->nullable()->comment('目标用户ID');
                $table->unsignedBigInteger('organization_id')->nullable()->comment('组织ID');
                $table->unsignedBigInteger('permission_id')->nullable()->comment('权限ID');
                $table->unsignedBigInteger('role_id')->nullable()->comment('角色ID');
                $table->enum('action', ['grant', 'revoke', 'update', 'inherit', 'override', 'template_apply'])->comment('操作类型');
                $table->enum('subject_type', ['user', 'role', 'organization', 'template'])->comment('主体类型');
                $table->unsignedBigInteger('subject_id')->comment('主体ID');
                $table->string('permission_name', 100)->nullable()->comment('权限名称快照');
                $table->json('old_values')->nullable()->comment('变更前的值JSON');
                $table->json('new_values')->nullable()->comment('变更后的值JSON');
                $table->text('reason')->nullable()->comment('操作原因');
                $table->string('ip_address', 45)->nullable()->comment('操作IP地址');
                $table->string('user_agent', 500)->nullable()->comment('用户代理');
                $table->json('context')->nullable()->comment('操作上下文JSON');
                $table->enum('result', ['success', 'failed', 'partial'])->default('success')->comment('操作结果');
                $table->text('error_message')->nullable()->comment('错误信息');
                $table->timestamps();

                // 索引
                $table->index(['user_id', 'created_at']);
                $table->index(['target_user_id', 'action']);
                $table->index(['organization_id', 'created_at']);
                $table->index(['permission_id', 'action']);
                $table->index(['subject_type', 'subject_id']);
                $table->index(['action', 'created_at']);
                $table->index(['result', 'created_at']);

                // 外键约束
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('target_user_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('set null');
                $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('set null');
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_audit_logs');
    }
};
