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
        Schema::create('district_boundaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('education_district_id')->comment('学区组织ID');
            $table->string('name', 100)->comment('边界名称');
            $table->json('boundary_points')->comment('边界坐标点集合');
            $table->decimal('center_latitude', 10, 8)->nullable()->comment('中心纬度');
            $table->decimal('center_longitude', 11, 8)->nullable()->comment('中心经度');
            $table->decimal('area_size', 10, 2)->nullable()->comment('覆盖面积(平方公里)');
            $table->integer('school_count')->default(0)->comment('包含学校数量');
            $table->integer('total_students')->default(0)->comment('总学生数');
            $table->text('description')->nullable()->comment('描述');
            $table->enum('status', ['active', 'inactive', 'draft'])->default('active')->comment('状态');
            $table->unsignedBigInteger('created_by')->nullable()->comment('创建者');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('更新者');
            $table->timestamps();

            $table->foreign('education_district_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['education_district_id', 'status']);
            $table->index(['center_latitude', 'center_longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('district_boundaries');
    }
};
