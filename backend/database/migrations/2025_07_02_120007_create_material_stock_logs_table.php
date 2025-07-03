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
        Schema::create('material_stock_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_code', 50)->unique()->comment('日志编号');
            $table->unsignedBigInteger('material_id')->comment('材料ID');
            $table->enum('operation_type', ['purchase', 'usage', 'adjustment', 'expired', 'damaged'])->comment('操作类型');
            $table->integer('quantity_before')->comment('操作前数量');
            $table->integer('quantity_change')->comment('变更数量(正数为增加，负数为减少)');
            $table->integer('quantity_after')->comment('操作后数量');
            $table->decimal('unit_price', 8, 2)->nullable()->comment('单价');
            $table->decimal('total_amount', 10, 2)->nullable()->comment('总金额');
            $table->text('reason')->comment('操作原因');
            $table->string('reference_type', 50)->nullable()->comment('关联类型');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('关联ID');
            $table->text('notes')->nullable()->comment('备注');
            $table->unsignedBigInteger('operator_id')->comment('操作人ID');
            $table->datetime('operated_at')->comment('操作时间');
            $table->unsignedBigInteger('organization_id')->comment('所属组织ID');
            $table->json('extra_data')->nullable()->comment('扩展数据');
            $table->timestamps();

            // 索引
            $table->index(['material_id', 'operated_at']);
            $table->index(['operation_type', 'operated_at']);
            $table->index(['operator_id', 'operated_at']);
            $table->index(['organization_id', 'operated_at']);
            $table->index(['reference_type', 'reference_id']);

            // 外键约束
            $table->foreign('material_id')->references('id')->on('materials')->onDelete('cascade');
            $table->foreign('operator_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_stock_logs');
    }
};
