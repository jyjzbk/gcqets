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
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('导入用户ID');
            $table->string('file_name')->comment('导入文件名');
            $table->string('file_path')->comment('导入文件路径');
            $table->string('import_type')->comment('导入类型');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending')->comment('导入状态');
            $table->integer('total_rows')->default(0)->comment('总行数');
            $table->integer('processed_rows')->default(0)->comment('已处理行数');
            $table->integer('success_rows')->default(0)->comment('成功行数');
            $table->integer('failed_rows')->default(0)->comment('失败行数');
            $table->json('error_details')->nullable()->comment('错误详情');
            $table->json('import_options')->nullable()->comment('导入选项');
            $table->timestamp('started_at')->nullable()->comment('开始时间');
            $table->timestamp('completed_at')->nullable()->comment('完成时间');
            $table->timestamps();

            // 外键约束
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};
