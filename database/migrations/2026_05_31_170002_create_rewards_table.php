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
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('loyalty_rule_id')->nullable()->constrained('loyalty_rules')->nullOnDelete();
            $table->string('type')->index();
            $table->string('value')->nullable();
            $table->string('status')->default('pending')->index();
            $table->timestamp('granted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};

