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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('角色名称');
            $table->string('display_name', 100)->comment('角色显示名称');
            $table->text('description')->nullable()->comment('角色描述');
            $table->enum('role_type', ['system', 'custom'])->default('custom')->comment('角色类型');
            $table->tinyInteger('level')->comment('角色级别 1-省级 2-市级 3-区县级 4-学区级 5-学校级');
            $table->json('applicable_org_types')->nullable()->comment('适用组织类型JSON数组');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('状态');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->boolean('is_default')->default(false)->comment('是否默认角色');
            $table->timestamps();

            // 索引
            $table->index(['level', 'status']);
            $table->index('role_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};