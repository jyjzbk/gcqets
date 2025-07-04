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
        // 添加实验计划表的外键约束
        Schema::table('experiment_plans', function (Blueprint $table) {
            $table->foreign('experiment_catalog_id')->references('id')->on('experiment_catalogs')->onDelete('restrict');
            $table->foreign('curriculum_standard_id')->references('id')->on('curriculum_standards')->onDelete('restrict');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        // 添加实验记录表的外键约束
        Schema::table('experiment_records', function (Blueprint $table) {
            $table->foreign('experiment_plan_id')->references('id')->on('experiment_plans')->onDelete('restrict');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });

        // 添加实验照片表的外键约束
        Schema::table('experiment_photos', function (Blueprint $table) {
            $table->foreign('experiment_record_id')->references('id')->on('experiment_records')->onDelete('cascade');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
        });

        // 添加审核日志表的外键约束
        Schema::table('experiment_review_logs', function (Blueprint $table) {
            $table->foreign('experiment_record_id')->references('id')->on('experiment_records')->onDelete('cascade');
            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 删除外键约束
        Schema::table('experiment_plans', function (Blueprint $table) {
            $table->dropForeign(['experiment_catalog_id']);
            $table->dropForeign(['curriculum_standard_id']);
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['approved_by']);
        });

        Schema::table('experiment_records', function (Blueprint $table) {
            $table->dropForeign(['experiment_plan_id']);
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['reviewed_by']);
        });

        Schema::table('experiment_photos', function (Blueprint $table) {
            $table->dropForeign(['experiment_record_id']);
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['created_by']);
        });

        Schema::table('experiment_review_logs', function (Blueprint $table) {
            $table->dropForeign(['experiment_record_id']);
            $table->dropForeign(['reviewer_id']);
            $table->dropForeign(['organization_id']);
        });
    }
};
