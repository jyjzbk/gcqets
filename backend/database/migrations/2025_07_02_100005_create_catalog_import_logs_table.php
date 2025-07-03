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
        Schema::create('catalog_import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('filename', 255)->comment('导入文件名');
            $table->bigInteger('file_size')->comment('文件大小（字节）');
            $table->string('file_path', 255)->comment('文件存储路径');
            $table->unsignedBigInteger('organization_id')->nullable()->comment('目标组织ID');
            $table->unsignedBigInteger('user_id')->comment('导入用户ID');
            $table->enum('import_type', ['standard_library', 'custom', 'template'])->comment('导入类型');
            $table->enum('status', ['pending', 'processing', 'success', 'partial_success', 'failed'])->default('pending')->comment('导入状态');
            $table->integer('total_rows')->default(0)->comment('总行数');
            $table->integer('success_rows')->default(0)->comment('成功行数');
            $table->integer('failed_rows')->default(0)->comment('失败行数');
            $table->integer('skipped_rows')->default(0)->comment('跳过行数');
            $table->json('error_details')->nullable()->comment('错误详情');
            $table->json('warning_details')->nullable()->comment('警告详情');
            $table->json('import_options')->nullable()->comment('导入选项');
            $table->json('validation_rules')->nullable()->comment('校验规则');
            $table->timestamp('started_at')->nullable()->comment('开始时间');
            $table->timestamp('completed_at')->nullable()->comment('完成时间');
            $table->timestamps();

            // 索引
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['organization_id', 'import_type']);
            $table->index(['import_type', 'status']);

            // 外键约束将在单独的迁移中添加
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_import_logs');
    }
};
