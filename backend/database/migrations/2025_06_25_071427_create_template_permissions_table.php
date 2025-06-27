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
        Schema::create('template_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id')->comment('权限模板ID');
            $table->unsignedBigInteger('permission_id')->comment('权限ID');
            $table->timestamps();

            // 外键约束
            $table->foreign('template_id')->references('id')->on('permission_templates')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');

            // 唯一约束
            $table->unique(['template_id', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_permissions');
    }
};
