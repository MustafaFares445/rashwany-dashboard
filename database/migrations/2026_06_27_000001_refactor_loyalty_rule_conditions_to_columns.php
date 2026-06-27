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
        Schema::table('loyalty_rules', function (Blueprint $table) {
            $table->decimal('threshold_hours', 10, 2)->nullable()->after('trigger_type');
            $table->unsignedInteger('threshold_visits')->nullable()->after('threshold_hours');
            $table->unsignedInteger('threshold_subscription_months')->nullable()->after('threshold_visits');
            $table->unsignedInteger('period_months')->nullable()->after('threshold_subscription_months');
            $table->text('description')->nullable()->after('period_months');
        });

        DB::table('loyalty_rules')
            ->whereNotNull('condition_json')
            ->orderBy('id')
            ->each(function (object $rule): void {
                $conditions = json_decode($rule->condition_json ?? '{}', true);
                $conditions = is_array($conditions) ? $conditions : [];

                DB::table('loyalty_rules')
                    ->where('id', $rule->id)
                    ->update([
                        'threshold_hours' => $conditions['min_hours'] ?? $conditions['threshold_hours'] ?? null,
                        'threshold_visits' => $conditions['min_visits'] ?? $conditions['threshold_visits'] ?? null,
                        'threshold_subscription_months' => $conditions['min_months'] ?? $conditions['subscription_months'] ?? null,
                        'period_months' => $conditions['period_months'] ?? null,
                    ]);
            });

        Schema::table('loyalty_rules', function (Blueprint $table) {
            $table->dropColumn('condition_json');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loyalty_rules', function (Blueprint $table) {
            $table->json('condition_json')->nullable()->after('trigger_type');
        });

        DB::table('loyalty_rules')
            ->orderBy('id')
            ->each(function (object $rule): void {
                $conditions = array_filter([
                    'min_hours' => $rule->threshold_hours,
                    'min_visits' => $rule->threshold_visits,
                    'subscription_months' => $rule->threshold_subscription_months,
                    'period_months' => $rule->period_months,
                ], fn ($value) => $value !== null);

                DB::table('loyalty_rules')
                    ->where('id', $rule->id)
                    ->update(['condition_json' => $conditions ? json_encode($conditions) : null]);
            });

        Schema::table('loyalty_rules', function (Blueprint $table) {
            $table->dropColumn([
                'threshold_hours',
                'threshold_visits',
                'threshold_subscription_months',
                'period_months',
                'description',
            ]);
        });
    }
};
