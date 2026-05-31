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
        Schema::create('daily_report_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('snapshot_date')->unique();
            $table->unsignedInteger('sessions_count')->default(0);
            $table->unsignedInteger('open_sessions_count')->default(0);
            $table->unsignedInteger('needs_review_sessions_count')->default(0);
            $table->decimal('revenue_paid_total', 12, 2)->default(0);
            $table->decimal('revenue_due_total', 12, 2)->default(0);
            $table->unsignedInteger('active_members_count')->default(0);
            $table->unsignedInteger('active_subscriptions_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_report_snapshots');
    }
};

