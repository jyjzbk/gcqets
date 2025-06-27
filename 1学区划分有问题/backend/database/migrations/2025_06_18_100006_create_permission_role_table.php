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
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permission_id')->comment('权限ID');
            $table->unsignedBigInteger('role_id')->comment('角色ID');
            $table->json('conditions')->nullable()->comment('权限条件JSON');
            $table->enum('access_type', ['allow', 'deny'])->default('allow')->comment('访问类型');
            $table->timestamps();

            // 索引
            $table->index(['permission_id', 'role_id']);
            $table->index(['role_id', 'access_type']);
            $table->unique(['permission_id', 'role_id'], 'unique_permission_role');

            // 外键约束
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_role');
    }
};