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
        Schema::create('organization_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->enum('access_type', ['allow', 'deny'])->default('allow')->comment('访问类型');
            $table->foreignId('granted_by')->nullable()->constrained('users')->onDelete('set null')->comment('授权人');
            $table->timestamp('granted_at')->nullable()->comment('授权时间');
            $table->timestamps();
            
            $table->unique(['organization_id', 'permission_id']);
            $table->index(['organization_id', 'access_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_permissions');
    }
};
