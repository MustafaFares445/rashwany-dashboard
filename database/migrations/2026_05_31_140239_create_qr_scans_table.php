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
        Schema::create('qr_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->foreignId('qr_code_id')->nullable()->constrained('qr_codes')->nullOnDelete();
            $table->string('purpose')->index();
            $table->string('result')->index();
            $table->string('failure_reason')->nullable();
            $table->timestamp('scanned_at');
            $table->string('ip_address')->nullable();
            $table->text('device_info')->nullable();
            $table->string('location_id')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_scans');
    }
};
