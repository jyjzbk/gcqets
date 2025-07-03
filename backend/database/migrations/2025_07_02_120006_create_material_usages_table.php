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
        Schema::create('material_usages', function (Blueprint $table) {
            $table->id();
            $table->string('usage_code', 50)->unique()->comment('使用编号');
            $table->unsignedBigInteger('material_id')->comment('材料ID');
            $table->unsignedBigInteger('user_id')->comment('使用人ID');
            $table->unsignedBigInteger('experiment_catalog_id')->nullable()->comment('关联实验目录ID');
            $table->integer('quantity_used')->comment('使用数量');
            $table->text('purpose')->comment('使用目的');
            $table->datetime('used_at')->comment('使用时间');
            $table->text('notes')->nullable()->comment('使用备注');
            $table->enum('usage_type', ['experiment', 'maintenance', 'teaching', 'other'])->default('experiment')->comment('使用类型');
            $table->string('class_name', 100)->nullable()->comment('班级名称');
            $table->integer('student_count')->nullable()->comment('学生人数');
            $table->unsignedBigInteger('organization_id')->comment('所属组织ID');
            $table->unsignedBigInteger('created_by')->comment('创建人ID');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('更新人ID');
            $table->json('extra_data')->nullable()->comment('扩展数据');
            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index(['material_id', 'used_at']);
            $table->index(['user_id', 'used_at']);
            $table->index(['experiment_catalog_id', 'used_at']);
            $table->index(['organization_id', 'used_at']);
            $table->index(['usage_type', 'used_at']);
            $table->index(['created_by', 'created_at']);

            // 外键约束
            $table->foreign('material_id')->references('id')->on('materials')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('experiment_catalog_id')->references('id')->on('experiment_catalogs')->onDelete('set null');
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
        Schema::dropIfExists('material_usages');
    }
};
