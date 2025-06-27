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
        Schema::create('school_import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('filename')->comment('导入文件名');
            $table->bigInteger('file_size')->comment('文件大小（字节）');
            $table->string('file_path')->comment('文件存储路径');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('父级组织ID');
            $table->unsignedBigInteger('user_id')->nullable()->comment('导入用户ID');
            $table->enum('status', ['pending', 'processing', 'success', 'partial_success', 'failed'])
                  ->default('pending')->comment('导入状态');
            $table->integer('total_rows')->default(0)->comment('总行数');
            $table->integer('success_rows')->default(0)->comment('成功行数');
            $table->integer('failed_rows')->default(0)->comment('失败行数');
            $table->json('error_details')->nullable()->comment('错误详情');
            $table->json('warning_details')->nullable()->comment('警告详情');
            $table->json('import_options')->nullable()->comment('导入选项');
            $table->timestamp('started_at')->nullable()->comment('开始时间');
            $table->timestamp('completed_at')->nullable()->comment('完成时间');
            $table->timestamps();

            // 索引
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index('parent_id');

            // 外键约束
            $table->foreign('parent_id')->references('id')->on('organizations')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_import_logs');
    }
};
