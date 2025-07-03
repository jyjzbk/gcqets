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
        Schema::create('curriculum_standards', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200)->comment('课程标准名称');
            $table->string('code', 50)->unique()->comment('标准编码');
            $table->string('version', 50)->comment('版本号');
            $table->enum('subject', ['physics', 'chemistry', 'biology', 'science'])->comment('学科');
            $table->enum('grade', ['grade1', 'grade2', 'grade3', 'grade4', 'grade5', 'grade6', 'grade7', 'grade8', 'grade9'])->comment('年级');
            $table->text('content')->nullable()->comment('标准内容');
            $table->text('learning_objectives')->nullable()->comment('学习目标');
            $table->text('key_concepts')->nullable()->comment('核心概念');
            $table->text('skills_requirements')->nullable()->comment('技能要求');
            $table->text('assessment_criteria')->nullable()->comment('评价标准');
            $table->date('effective_date')->nullable()->comment('生效日期');
            $table->date('expiry_date')->nullable()->comment('失效日期');
            $table->enum('status', ['active', 'inactive', 'draft', 'archived'])->default('draft')->comment('状态');
            $table->unsignedBigInteger('organization_id')->comment('所属组织ID');
            $table->unsignedBigInteger('created_by')->comment('创建人ID');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('更新人ID');
            $table->json('extra_data')->nullable()->comment('扩展数据');
            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index(['subject', 'grade', 'version']);
            $table->index(['organization_id', 'status']);
            $table->index(['effective_date', 'expiry_date']);
            $table->index(['created_by', 'created_at']);

            // 外键约束将在单独的迁移中添加
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curriculum_standards');
    }
};
