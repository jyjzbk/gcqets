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
        if (!Schema::hasTable('permission_templates')) {
            Schema::create('permission_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100)->comment('模板名称');
                $table->string('code', 50)->unique()->comment('模板代码');
                $table->text('description')->nullable()->comment('模板描述');
                $table->unsignedBigInteger('created_by')->comment('创建者ID');
                $table->unsignedBigInteger('updated_by')->nullable()->comment('更新者ID');
                $table->unsignedTinyInteger('level')->default(5)->comment('适用级别');
                $table->json('applicable_org_types')->nullable()->comment('适用组织类型JSON数组');
                $table->boolean('is_system')->default(false)->comment('是否系统模板');
                $table->boolean('is_active')->default(true)->comment('是否启用');
                $table->integer('sort_order')->default(0)->comment('排序');
                $table->string('version', 20)->default('1.0.0')->comment('模板版本');
                $table->json('metadata')->nullable()->comment('模板元数据');
                $table->timestamps();
                $table->softDeletes();

                // 索引
                $table->index(['level', 'is_active']);
                $table->index(['is_system', 'is_active']);
                $table->index('sort_order');

                // 外键约束
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_templates');
    }
};
