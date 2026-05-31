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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->index();
            $table->string('duration_unit');
            $table->unsignedInteger('duration_value')->nullable();
            $table->decimal('included_hours', 10, 2)->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('renewal_type')->default('manual');
            $table->boolean('is_active')->default(true);
            $table->json('settings_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
