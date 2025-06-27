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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('组织机构名称');
            $table->string('code', 50)->unique()->comment('组织机构编码');
            $table->enum('type', ['province', 'city', 'district', 'education_zone', 'school'])->comment('组织类型');
            $table->tinyInteger('level')->comment('组织层级 1-省 2-市 3-区县 4-学区 5-学校');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('父级组织ID');
            $table->string('full_path', 500)->nullable()->comment('完整路径 如:1/2/3/4/5');
            $table->text('description')->nullable()->comment('组织描述');
            $table->string('contact_person', 50)->nullable()->comment('联系人');
            $table->string('contact_phone', 20)->nullable()->comment('联系电话');
            $table->string('address', 200)->nullable()->comment('地址');
            $table->decimal('longitude', 10, 7)->nullable()->comment('经度');
            $table->decimal('latitude', 10, 7)->nullable()->comment('纬度');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->comment('状态');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->json('extra_data')->nullable()->comment('扩展数据JSON');
            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index(['parent_id', 'level']);
            $table->index(['type', 'level']);
            $table->index(['status', 'level']);
            $table->index('full_path');
            
            // 外键约束
            $table->foreign('parent_id')->references('id')->on('organizations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};