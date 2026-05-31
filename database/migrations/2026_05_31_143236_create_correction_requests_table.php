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
        Schema::create('correction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('session_id')->nullable()->constrained('attendance_sessions')->nullOnDelete();
            $table->string('type')->index();
            $table->timestamp('requested_check_in_at')->nullable();
            $table->timestamp('requested_check_out_at')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correction_requests');
    }
};
