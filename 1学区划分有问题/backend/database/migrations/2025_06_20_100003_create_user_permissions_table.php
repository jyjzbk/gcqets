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
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->unsignedBigInteger('permission_id')->comment('权限ID');
            $table->unsignedBigInteger('organization_id')->nullable()->comment('组织机构ID');
            $table->unsignedBigInteger('granted_by')->nullable()->comment('授权人ID');
            $table->timestamp('granted_at')->nullable()->comment('授权时间');
            $table->timestamp('expires_at')->nullable()->comment('过期时间');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('状态');
            $table->timestamps();

            // 外键约束
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('granted_by')->references('id')->on('users')->onDelete('set null');
            
            // 索引
            $table->index(['user_id', 'permission_id']);
            $table->index(['organization_id']);
            $table->unique(['user_id', 'permission_id', 'organization_id'], 'unique_user_permission_org');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
