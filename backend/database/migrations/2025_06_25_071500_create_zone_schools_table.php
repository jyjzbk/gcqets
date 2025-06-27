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
        Schema::create('zone_schools', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zone_id')->comment('学区ID');
            $table->unsignedBigInteger('school_id')->comment('学校ID');
            $table->enum('assignment_type', ['auto', 'manual'])->default('auto')->comment('分配类型');
            $table->decimal('distance', 10, 2)->nullable()->comment('距离学区中心点距离(公里)');
            $table->text('assignment_reason')->nullable()->comment('分配原因');
            $table->unsignedBigInteger('assigned_by')->nullable()->comment('分配人ID');
            $table->timestamp('assigned_at')->nullable()->comment('分配时间');
            $table->timestamps();

            // 外键约束
            $table->foreign('zone_id')->references('id')->on('education_zones')->onDelete('cascade');
            $table->foreign('school_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');

            // 唯一约束
            $table->unique(['zone_id', 'school_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zone_schools');
    }
};
