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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_type')->default('admin');
            $table->string('action');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->json('old_values_json')->nullable();
            $table->json('new_values_json')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
