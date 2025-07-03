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
        Schema::create('equipment_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('分类名称');
            $table->string('code', 50)->unique()->comment('分类编码');
            $table->text('description')->nullable()->comment('分类描述');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('父级分类ID');
            $table->string('subject', 50)->nullable()->comment('适用学科');
            $table->string('grade_range', 100)->nullable()->comment('适用年级范围');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('状态');
            $table->unsignedBigInteger('organization_id')->comment('所属组织ID');
            $table->unsignedBigInteger('created_by')->comment('创建人ID');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('更新人ID');
            $table->json('extra_data')->nullable()->comment('扩展数据');
            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index(['parent_id', 'status']);
            $table->index(['organization_id', 'status']);
            $table->index(['subject', 'grade_range']);
            $table->index(['created_by', 'created_at']);

            // 外键约束
            $table->foreign('parent_id')->references('id')->on('equipment_categories')->onDelete('cascade');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_categories');
    }
};
