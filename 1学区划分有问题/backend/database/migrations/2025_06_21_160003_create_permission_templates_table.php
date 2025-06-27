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
        Schema::create('permission_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('模板名称');
            $table->string('display_name', 150)->comment('模板显示名称');
            $table->text('description')->nullable()->comment('模板描述');
            $table->enum('template_type', ['role', 'organization', 'user'])->comment('模板类型');
            $table->tinyInteger('target_level')->nullable()->comment('目标级别');
            $table->json('permission_ids')->comment('权限ID数组');
            $table->json('conditions')->nullable()->comment('应用条件');
            $table->boolean('is_system')->default(false)->comment('是否系统模板');
            $table->boolean('is_default')->default(false)->comment('是否默认模板');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('状态');
            $table->unsignedBigInteger('created_by')->nullable()->comment('创建者ID');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('更新者ID');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->timestamps();

            // 外键约束
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            // 索引
            $table->index(['template_type', 'target_level']);
            $table->index(['is_system', 'is_default']);
            $table->index(['status', 'sort_order']);
            $table->unique(['name', 'template_type'], 'uniq_template_name_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_templates');
    }
};
