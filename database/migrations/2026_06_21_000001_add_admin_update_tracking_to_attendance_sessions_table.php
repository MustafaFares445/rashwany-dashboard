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
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->foreignId('admin_updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('admin_updated_at')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_updated_by');
            $table->dropColumn('admin_updated_at');
        });
    }
};
