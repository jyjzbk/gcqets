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
        Schema::create('school_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->comment('学校组织ID');
            $table->decimal('latitude', 10, 8)->nullable()->comment('纬度');
            $table->decimal('longitude', 11, 8)->nullable()->comment('经度');
            $table->string('address', 500)->nullable()->comment('详细地址');
            $table->string('province', 50)->nullable()->comment('省份');
            $table->string('city', 50)->nullable()->comment('城市');
            $table->string('district', 50)->nullable()->comment('区县');
            $table->string('street', 100)->nullable()->comment('街道');
            $table->string('postal_code', 10)->nullable()->comment('邮政编码');
            $table->integer('student_count')->default(0)->comment('学生数量');
            $table->integer('teacher_count')->default(0)->comment('教师数量');
            $table->integer('class_count')->default(0)->comment('班级数量');
            $table->decimal('area_size', 8, 2)->nullable()->comment('学校面积(平方米)');
            $table->enum('school_type', ['primary', 'middle', 'high', 'mixed'])->default('primary')->comment('学校类型');
            $table->json('facilities')->nullable()->comment('设施信息');
            $table->text('remarks')->nullable()->comment('备注');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->unique('organization_id');
            $table->index(['latitude', 'longitude']);
            $table->index(['province', 'city', 'district']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_locations');
    }
};
