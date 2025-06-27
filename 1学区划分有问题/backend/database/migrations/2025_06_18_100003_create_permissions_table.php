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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->comment('权限名称');
            $table->string('display_name', 150)->comment('权限显示名称');
            $table->string('module', 50)->comment('所属模块');
            $table->string('action', 50)->comment('操作动作');
            $table->text('description')->nullable()->comment('权限描述');
            $table->tinyInteger('min_level')->comment('最小需要级别');
            $table->json('applicable_levels')->comment('适用级别JSON数组');
            $table->enum('scope_type', ['self', 'direct_subordinates', 'all_subordinates', 'same_level', 'cross_level'])->comment('权限范围类型');
            $table->boolean('is_system')->default(false)->comment('是否系统权限');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('状态');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->timestamps();

            // 索引
            $table->index(['module', 'action']);
            $table->index(['min_level', 'status']);
            $table->index('scope_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};