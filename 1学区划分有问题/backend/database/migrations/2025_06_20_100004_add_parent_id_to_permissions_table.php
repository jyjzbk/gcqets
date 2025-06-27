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
        Schema::table('permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('id')->comment('父级权限ID');
            $table->string('path')->nullable()->after('parent_id')->comment('权限路径');
            $table->string('group')->nullable()->after('module')->comment('权限分组');
            $table->string('resource')->nullable()->after('action')->comment('资源');
            $table->string('method')->nullable()->after('resource')->comment('HTTP方法');
            $table->string('route')->nullable()->after('method')->comment('路由名称');
            $table->string('icon')->nullable()->after('route')->comment('图标');
            $table->boolean('is_menu')->default(false)->after('is_system')->comment('是否为菜单');
            
            // 添加外键约束
            $table->foreign('parent_id')->references('id')->on('permissions')->onDelete('cascade');
            
            // 添加索引
            $table->index(['parent_id']);
            $table->index(['group']);
            $table->index(['module']);
            $table->index(['is_menu']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn([
                'parent_id',
                'path',
                'group',
                'resource',
                'method',
                'route',
                'icon',
                'is_menu'
            ]);
        });
    }
};
