<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id')->comment('id');
            $table->unsignedBigInteger('branch_id')->comment('branch_id');
            $table->string('name')->comment('name');
            $table->string('code')->unique()->comment('code');
            $table->time('start_time')->comment('start_time');
            $table->time('end_time')->comment('end_time');
            $table->integer('grace_period_minutes')->default(15)->comment('grace_period_minutes');
            $table->json('working_days')->nullable()->comment('working_days');
            $table->boolean('is_active')->default(true)->comment('is_active');
            $table->text('description')->nullable()->comment('description');
            $table->timestamps();
            $table->index('created_at');
            $table->index('updated_at');
            $table->softDeletes();
            $table->index('deleted_at');

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->index('branch_id');
        });

        Schema::create('employee_shifts', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id')->comment('id');
            $table->unsignedBigInteger('employee_id')->comment('employee_id');
            $table->unsignedBigInteger('shift_id')->comment('shift_id');
            $table->date('start_date')->comment('start_date');
            $table->date('end_date')->nullable()->comment('end_date');
            $table->boolean('is_active')->default(true)->comment('is_active');
            $table->timestamps();
            $table->index('created_at');
            $table->index('updated_at');
            $table->softDeletes();
            $table->index('deleted_at');

            $table->foreign('employee_id')->references('id')->on('hr_employees')->onDelete('cascade');
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
            $table->index('employee_id');
            $table->index('shift_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_shifts');
        Schema::dropIfExists('shifts');
    }
};
