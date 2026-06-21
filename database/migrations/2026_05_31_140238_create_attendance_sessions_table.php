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
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->timestamp('check_in_at');
            $table->timestamp('check_out_at')->nullable();
            $table->unsignedInteger('raw_duration_minutes')->nullable();
            $table->unsignedInteger('raw_duration_seconds')->nullable();
            $table->unsignedInteger('billable_duration_minutes')->nullable();
            $table->unsignedInteger('billable_duration_seconds')->nullable();
            $table->timestamp('rounded_from_at')->nullable();
            $table->timestamp('rounded_to_at')->nullable();
            $table->string('status')->default('open')->index();
            $table->foreignId('check_in_scan_id')->nullable()->index();
            $table->foreignId('check_out_scan_id')->nullable()->index();
            $table->unsignedBigInteger('correction_request_id')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
