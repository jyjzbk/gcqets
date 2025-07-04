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
        Schema::create('experiment_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('experiment_record_id')->comment('实验记录ID');
            $table->enum('photo_type', [
                'preparation',  // 准备阶段
                'process',      // 过程记录
                'result',       // 结果展示
                'equipment',    // 器材准备
                'safety'        // 安全记录
            ])->comment('照片类型');
            $table->string('file_path', 500)->comment('文件路径');
            $table->string('file_name', 255)->comment('文件名');
            $table->string('original_name', 255)->comment('原始文件名');
            $table->integer('file_size')->comment('文件大小(字节)');
            $table->string('mime_type', 100)->comment('MIME类型');
            $table->integer('width')->nullable()->comment('图片宽度');
            $table->integer('height')->nullable()->comment('图片高度');
            $table->enum('upload_method', ['mobile', 'web'])->default('web')->comment('上传方式');
            $table->json('location_info')->nullable()->comment('位置信息(经纬度)');
            $table->json('timestamp_info')->nullable()->comment('时间戳信息');
            $table->boolean('watermark_applied')->default(false)->comment('是否已添加水印');
            $table->json('ai_analysis_result')->nullable()->comment('AI分析结果');
            $table->enum('compliance_status', [
                'pending',      // 待检查
                'compliant',    // 合规
                'non_compliant',// 不合规
                'needs_review'  // 需要人工审核
            ])->default('pending')->comment('合规状态');
            $table->text('description')->nullable()->comment('照片描述');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->boolean('is_required')->default(false)->comment('是否必需照片');
            $table->string('hash', 64)->nullable()->comment('文件哈希值');
            $table->json('exif_data')->nullable()->comment('EXIF数据');
            $table->unsignedBigInteger('organization_id')->comment('所属组织ID');
            $table->unsignedBigInteger('created_by')->comment('创建人ID');
            $table->json('extra_data')->nullable()->comment('扩展数据');
            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index(['experiment_record_id', 'photo_type']);
            $table->index(['compliance_status', 'created_at']);
            $table->index(['organization_id', 'photo_type']);
            $table->index(['created_by', 'created_at']);
            $table->index(['upload_method', 'created_at']);
            $table->index(['hash']); // 用于去重检查

            // 外键约束将在单独的迁移中添加
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiment_photos');
    }
};
