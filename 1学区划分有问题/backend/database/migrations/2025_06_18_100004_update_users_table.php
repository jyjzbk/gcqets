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
            // 添加组织机构关联字段
            $table->unsignedBigInteger('organization_id')->nullable()->after('email')->comment('所属组织机构ID');
            $table->string('employee_id', 50)->nullable()->after('organization_id')->comment('工号/学号');
            $table->enum('user_type', ['admin', 'teacher', 'student', 'supervisor'])->default('teacher')->after('employee_id')->comment('用户类型');
            $table->enum('status', ['active', 'inactive', 'locked', 'pending'])->default('pending')->after('user_type')->comment('用户状态');
            
            // 个人信息字段
            $table->string('real_name', 50)->nullable()->after('name')->comment('真实姓名');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('real_name')->comment('性别');
            $table->date('birth_date')->nullable()->after('gender')->comment('出生日期');
            $table->string('phone', 20)->nullable()->after('birth_date')->comment('手机号码');
            $table->string('id_card', 18)->nullable()->after('phone')->comment('身份证号');
            $table->text('address')->nullable()->after('id_card')->comment('住址');
            
            // 职业信息字段
            $table->string('department', 100)->nullable()->after('address')->comment('部门');
            $table->string('position', 100)->nullable()->after('department')->comment('职位');
            $table->string('title', 100)->nullable()->after('position')->comment('职称');
            $table->date('hire_date')->nullable()->after('title')->comment('入职日期');
            
            // 系统字段
            $table->timestamp('last_login_at')->nullable()->after('hire_date')->comment('最后登录时间');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at')->comment('最后登录IP');
            $table->json('preferences')->nullable()->after('last_login_ip')->comment('用户偏好设置');
            $table->text('remarks')->nullable()->after('preferences')->comment('备注');
            
            $table->softDeletes()->after('updated_at');
            
            // 索引
            $table->index('organization_id');
            $table->index('employee_id');
            $table->index(['user_type', 'status']);
            $table->index('phone');
            $table->unique(['employee_id', 'organization_id'], 'unique_employee_org');
            
            // 外键约束
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id']);
            $table->dropIndex(['employee_id']);
            $table->dropIndex(['user_type', 'status']);
            $table->dropIndex(['phone']);
            $table->dropUnique('unique_employee_org');
            
            $table->dropColumn([
                'organization_id', 'employee_id', 'user_type', 'status',
                'real_name', 'gender', 'birth_date', 'phone', 'id_card', 'address',
                'department', 'position', 'title', 'hire_date',
                'last_login_at', 'last_login_ip', 'preferences', 'remarks',
                'deleted_at'
            ]);
        });
    }
};