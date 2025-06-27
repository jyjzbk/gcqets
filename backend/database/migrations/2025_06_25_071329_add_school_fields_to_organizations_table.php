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
        Schema::table('organizations', function (Blueprint $table) {
            // Add school-specific fields
            $table->integer('student_count')->nullable()->comment('学生数量');
            $table->decimal('campus_area', 10, 2)->nullable()->comment('校园面积(平方米)');
            $table->string('school_code', 50)->nullable()->comment('学校代码');
            $table->string('principal_name', 50)->nullable()->comment('校长姓名');
            $table->string('principal_phone', 20)->nullable()->comment('校长联系电话');
            $table->string('principal_email', 100)->nullable()->comment('校长邮箱');
            $table->year('founded_year')->nullable()->comment('建校年份');
            $table->enum('school_type', ['public', 'private', 'other'])->nullable()->comment('学校类型');
            $table->enum('education_level', ['primary', 'middle', 'high', 'vocational', 'comprehensive'])->nullable()->comment('教育阶段');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Remove school-specific fields
            $table->dropColumn([
                'student_count',
                'campus_area',
                'school_code',
                'principal_name',
                'principal_phone',
                'principal_email',
                'founded_year',
                'school_type',
                'education_level'
            ]);
        });
    }
};
