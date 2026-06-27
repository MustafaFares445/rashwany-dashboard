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
        Schema::create('loyalty_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trigger_type')->index();
            $table->decimal('min_total_hours', 10, 2)->nullable();
            $table->unsignedSmallInteger('period_months')->nullable();
            $table->unsignedSmallInteger('min_subscription_months')->nullable();
            $table->unsignedInteger('min_visit_count')->nullable();
            $table->string('reward_type')->index();
            $table->string('reward_value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['trigger_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_rules');
    }
};
