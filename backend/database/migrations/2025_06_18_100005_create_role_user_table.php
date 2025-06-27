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
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->unsignedBigInteger('role_id')->comment('角色ID');
            $table->unsignedBigInteger('organization_id')->nullable()->comment('授权组织范围ID');
            $table->enum('scope_type', ['current_org', 'direct_subordinates', 'all_subordinates'])->default('current_org')->comment('权限范围类型');
            $table->date('effective_date')->nullable()->comment('生效日期');
            $table->date('expiry_date')->nullable()->comment('失效日期');
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active')->comment('状态');
            $table->text('remarks')->nullable()->comment('备注');
            $table->unsignedBigInteger('created_by')->nullable()->comment('创建人ID');
            $table->timestamps();

            // 索引
            $table->index(['user_id', 'status']);
            $table->index(['role_id', 'status']);
            $table->index(['organization_id', 'scope_type']);
            $table->index(['effective_date', 'expiry_date']);
            $table->unique(['user_id', 'role_id', 'organization_id'], 'unique_user_role_org');

            // 外键约束
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
};