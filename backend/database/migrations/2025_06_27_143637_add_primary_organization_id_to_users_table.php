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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('primary_organization_id')->nullable()->after('organization_id')->comment('主要组织机构ID');
            $table->foreign('primary_organization_id')->references('id')->on('organizations')->onDelete('set null');
            $table->index('primary_organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['primary_organization_id']);
            $table->dropIndex(['primary_organization_id']);
            $table->dropColumn('primary_organization_id');
        });
    }
};
