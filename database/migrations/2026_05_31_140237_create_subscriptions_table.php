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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->string('status')->default('active')->index();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->decimal('total_hours', 10, 2)->nullable();
            $table->decimal('remaining_hours', 10, 2)->nullable();
            $table->decimal('used_hours', 10, 2)->default(0);
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('due_amount', 10, 2)->default(0);
            $table->boolean('auto_renew')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
