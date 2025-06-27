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
        Schema::create('education_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('学区名称');
            $table->string('code')->unique()->comment('学区编码');
            $table->unsignedBigInteger('district_id')->comment('所属区县ID');
            $table->text('description')->nullable()->comment('学区描述');
            $table->string('boundary_points')->nullable()->comment('边界点坐标集合');
            $table->decimal('center_longitude', 10, 7)->nullable()->comment('中心点经度');
            $table->decimal('center_latitude', 10, 7)->nullable()->comment('中心点纬度');
            $table->decimal('area', 10, 2)->nullable()->comment('面积(平方公里)');
            $table->integer('school_count')->default(0)->comment('学校数量');
            $table->integer('student_capacity')->default(0)->comment('学生容量');
            $table->integer('current_students')->default(0)->comment('当前学生数');
            $table->unsignedBigInteger('manager_id')->nullable()->comment('学区管理员ID');
            $table->string('manager_name')->nullable()->comment('学区管理员姓名');
            $table->string('manager_phone')->nullable()->comment('学区管理员电话');
            $table->enum('status', ['active', 'inactive', 'planning'])->default('active')->comment('状态');
            $table->json('extra_data')->nullable()->comment('扩展数据');
            $table->timestamps();
            $table->softDeletes();

            // 外键约束
            $table->foreign('district_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('education_zones');
    }
};
