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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200)->comment('设备名称');
            $table->string('code', 100)->unique()->comment('设备编码');
            $table->string('model', 100)->nullable()->comment('设备型号');
            $table->string('brand', 100)->nullable()->comment('品牌');
            $table->text('description')->nullable()->comment('设备描述');
            $table->unsignedBigInteger('category_id')->comment('设备分类ID');
            $table->string('serial_number', 100)->nullable()->comment('序列号');
            $table->decimal('purchase_price', 10, 2)->nullable()->comment('采购价格');
            $table->date('purchase_date')->nullable()->comment('采购日期');
            $table->string('supplier', 200)->nullable()->comment('供应商');
            $table->date('warranty_date')->nullable()->comment('保修期至');
            $table->enum('status', ['available', 'borrowed', 'maintenance', 'damaged', 'scrapped'])->default('available')->comment('设备状态');
            $table->string('location', 200)->nullable()->comment('存放位置');
            $table->text('usage_notes')->nullable()->comment('使用说明');
            $table->text('maintenance_notes')->nullable()->comment('维护记录');
            $table->integer('total_usage_count')->default(0)->comment('总使用次数');
            $table->integer('total_usage_hours')->default(0)->comment('总使用时长(小时)');
            $table->date('last_maintenance_date')->nullable()->comment('最后维护日期');
            $table->date('next_maintenance_date')->nullable()->comment('下次维护日期');
            $table->unsignedBigInteger('organization_id')->comment('所属组织ID');
            $table->unsignedBigInteger('created_by')->comment('创建人ID');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('更新人ID');
            $table->json('extra_data')->nullable()->comment('扩展数据');
            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index(['category_id', 'status']);
            $table->index(['organization_id', 'status']);
            $table->index(['status', 'location']);
            $table->index(['purchase_date', 'warranty_date']);
            $table->index(['created_by', 'created_at']);

            // 外键约束
            $table->foreign('category_id')->references('id')->on('equipment_categories')->onDelete('cascade');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
