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
        Schema::create('district_assignment_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->comment('学校ID');
            $table->unsignedBigInteger('old_district_id')->nullable()->comment('原学区ID');
            $table->unsignedBigInteger('new_district_id')->comment('新学区ID');
            $table->enum('assignment_type', ['auto', 'manual', 'import'])->comment('划分类型');
            $table->text('reason')->nullable()->comment('调整原因');
            $table->json('assignment_data')->nullable()->comment('划分数据(距离、规模等)');
            $table->unsignedBigInteger('operated_by')->comment('操作人');
            $table->timestamp('effective_date')->nullable()->comment('生效日期');
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('old_district_id')->references('id')->on('organizations')->onDelete('set null');
            $table->foreign('new_district_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('operated_by')->references('id')->on('users')->onDelete('cascade');

            $table->index(['school_id', 'created_at'], 'idx_school_created');
            $table->index(['new_district_id', 'assignment_type'], 'idx_district_type');
            $table->index('effective_date', 'idx_effective_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('district_assignment_history');
    }
};
