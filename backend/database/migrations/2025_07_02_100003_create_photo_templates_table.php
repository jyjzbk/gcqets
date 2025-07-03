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
        Schema::create('photo_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200)->comment('模板名称');
            $table->string('code', 50)->unique()->comment('模板编码');
            $table->enum('subject', ['physics', 'chemistry', 'biology', 'science'])->comment('学科');
            $table->enum('grade', ['grade1', 'grade2', 'grade3', 'grade4', 'grade5', 'grade6', 'grade7', 'grade8', 'grade9'])->comment('年级');
            $table->string('textbook_version', 100)->comment('教材版本');
            $table->enum('experiment_type', ['demonstration', 'group', 'individual', 'inquiry'])->comment('实验类型');
            $table->text('description')->nullable()->comment('模板描述');
            $table->json('required_photos')->comment('必需照片配置');
            $table->json('optional_photos')->nullable()->comment('可选照片配置');
            $table->json('photo_specifications')->nullable()->comment('照片规格要求');
            $table->enum('status', ['active', 'inactive', 'draft'])->default('draft')->comment('状态');
            $table->unsignedBigInteger('organization_id')->comment('所属组织ID');
            $table->unsignedBigInteger('created_by')->comment('创建人ID');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('更新人ID');
            $table->json('extra_data')->nullable()->comment('扩展数据');
            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index(['subject', 'grade', 'textbook_version']);
            $table->index(['organization_id', 'status']);
            $table->index(['experiment_type', 'status']);
            $table->index(['created_by', 'created_at']);

            // 外键约束将在单独的迁移中添加
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_templates');
    }
};
