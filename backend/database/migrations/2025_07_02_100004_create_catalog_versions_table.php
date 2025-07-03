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
        Schema::create('catalog_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('catalog_id')->comment('实验目录ID');
            $table->string('version', 50)->comment('版本号');
            $table->text('version_description')->nullable()->comment('版本描述');
            $table->json('changes')->nullable()->comment('变更内容');
            $table->json('catalog_data')->comment('目录数据快照');
            $table->enum('change_type', ['create', 'update', 'delete', 'restore'])->comment('变更类型');
            $table->text('change_reason')->nullable()->comment('变更原因');
            $table->enum('status', ['active', 'archived', 'rollback'])->default('active')->comment('状态');
            $table->unsignedBigInteger('created_by')->comment('创建人ID');
            $table->timestamp('created_at');

            // 索引
            $table->index(['catalog_id', 'version']);
            $table->index(['catalog_id', 'created_at']);
            $table->index(['change_type', 'status']);
            $table->index(['created_by', 'created_at']);

            // 外键约束将在单独的迁移中添加
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_versions');
    }
};
