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
        if (!Schema::hasTable('permission_inheritance')) {
            Schema::create('permission_inheritance', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('parent_organization_id')->comment('父级组织ID');
                $table->unsignedBigInteger('child_organization_id')->comment('子级组织ID');
                $table->unsignedBigInteger('permission_id')->comment('权限ID');
                $table->enum('inheritance_type', ['direct', 'indirect', 'override'])->default('direct')->comment('继承类型');
                $table->json('inheritance_path')->nullable()->comment('继承路径JSON数组');
                $table->boolean('is_overridden')->default(false)->comment('是否被覆盖');
                $table->unsignedBigInteger('overridden_by')->nullable()->comment('覆盖者ID');
                $table->timestamp('overridden_at')->nullable()->comment('覆盖时间');
                $table->text('override_reason')->nullable()->comment('覆盖原因');
                $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->comment('状态');
                $table->json('conditions')->nullable()->comment('继承条件JSON');
                $table->timestamps();

                // 索引
                $table->index(['parent_organization_id', 'child_organization_id']);
                $table->index(['child_organization_id', 'permission_id']);
                $table->index(['permission_id', 'status']);
                $table->index(['inheritance_type', 'status']);
                $table->index('is_overridden');

                // 唯一约束
                $table->unique(['parent_organization_id', 'child_organization_id', 'permission_id'], 'unique_inheritance');

                // 外键约束
                $table->foreign('parent_organization_id')->references('id')->on('organizations')->onDelete('cascade');
                $table->foreign('child_organization_id')->references('id')->on('organizations')->onDelete('cascade');
                $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
                $table->foreign('overridden_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_inheritance');
    }
};
