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
        Schema::create('organization_import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('filename')->comment('导入文件名');
            $table->unsignedBigInteger('user_id')->comment('导入用户ID');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('父级组织ID');
            $table->integer('total_rows')->default(0)->comment('总行数');
            $table->integer('success_count')->default(0)->comment('成功数量');
            $table->integer('failed_count')->default(0)->comment('失败数量');
            $table->json('errors')->nullable()->comment('错误信息');
            $table->json('warnings')->nullable()->comment('警告信息');
            $table->enum('status', ['processing', 'completed', 'failed'])->default('processing')->comment('状态');
            $table->text('remarks')->nullable()->comment('备注');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('organizations')->onDelete('set null');

            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_import_logs');
    }
};
