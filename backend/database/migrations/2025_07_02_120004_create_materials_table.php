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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200)->comment('材料名称');
            $table->string('code', 100)->unique()->comment('材料编码');
            $table->string('specification', 200)->nullable()->comment('规格型号');
            $table->string('brand', 100)->nullable()->comment('品牌');
            $table->text('description')->nullable()->comment('材料描述');
            $table->unsignedBigInteger('category_id')->comment('材料分类ID');
            $table->string('unit', 20)->comment('计量单位');
            $table->decimal('unit_price', 8, 2)->nullable()->comment('单价');
            $table->integer('current_stock')->default(0)->comment('当前库存');
            $table->integer('min_stock')->default(0)->comment('最低库存');
            $table->integer('max_stock')->nullable()->comment('最高库存');
            $table->integer('total_purchased')->default(0)->comment('累计采购数量');
            $table->integer('total_consumed')->default(0)->comment('累计消耗数量');
            $table->string('storage_location', 200)->nullable()->comment('存储位置');
            $table->text('storage_conditions')->nullable()->comment('存储条件');
            $table->date('expiry_date')->nullable()->comment('有效期至');
            $table->text('safety_notes')->nullable()->comment('安全注意事项');
            $table->enum('status', ['active', 'inactive', 'expired', 'out_of_stock'])->default('active')->comment('状态');
            $table->string('supplier', 200)->nullable()->comment('供应商');
            $table->date('last_purchase_date')->nullable()->comment('最后采购日期');
            $table->unsignedBigInteger('organization_id')->comment('所属组织ID');
            $table->unsignedBigInteger('created_by')->comment('创建人ID');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('更新人ID');
            $table->json('extra_data')->nullable()->comment('扩展数据');
            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index(['category_id', 'status']);
            $table->index(['organization_id', 'status']);
            $table->index(['current_stock', 'min_stock']);
            $table->index(['expiry_date', 'status']);
            $table->index(['created_by', 'created_at']);

            // 外键约束
            $table->foreign('category_id')->references('id')->on('material_categories')->onDelete('cascade');
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
        Schema::dropIfExists('materials');
    }
};
