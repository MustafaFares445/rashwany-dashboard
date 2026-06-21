<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('attendance_sessions', 'raw_duration_seconds')) {
                $table->unsignedInteger('raw_duration_seconds')->nullable()->after('raw_duration_minutes');
            }

            if (! Schema::hasColumn('attendance_sessions', 'billable_duration_seconds')) {
                $table->unsignedInteger('billable_duration_seconds')->nullable()->after('billable_duration_minutes');
            }
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE subscriptions MODIFY total_hours DECIMAL(10,4) NULL');
            DB::statement('ALTER TABLE subscriptions MODIFY remaining_hours DECIMAL(10,4) NULL');
            DB::statement('ALTER TABLE subscriptions MODIFY used_hours DECIMAL(10,4) NOT NULL DEFAULT 0');
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE subscriptions ALTER COLUMN total_hours TYPE DECIMAL(10,4)');
            DB::statement('ALTER TABLE subscriptions ALTER COLUMN remaining_hours TYPE DECIMAL(10,4)');
            DB::statement('ALTER TABLE subscriptions ALTER COLUMN used_hours TYPE DECIMAL(10,4)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_sessions', 'raw_duration_seconds')) {
                $table->dropColumn('raw_duration_seconds');
            }

            if (Schema::hasColumn('attendance_sessions', 'billable_duration_seconds')) {
                $table->dropColumn('billable_duration_seconds');
            }
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE subscriptions MODIFY total_hours DECIMAL(10,2) NULL');
            DB::statement('ALTER TABLE subscriptions MODIFY remaining_hours DECIMAL(10,2) NULL');
            DB::statement('ALTER TABLE subscriptions MODIFY used_hours DECIMAL(10,2) NOT NULL DEFAULT 0');
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE subscriptions ALTER COLUMN total_hours TYPE DECIMAL(10,2)');
            DB::statement('ALTER TABLE subscriptions ALTER COLUMN remaining_hours TYPE DECIMAL(10,2)');
            DB::statement('ALTER TABLE subscriptions ALTER COLUMN used_hours TYPE DECIMAL(10,2)');
        }
    }
};
