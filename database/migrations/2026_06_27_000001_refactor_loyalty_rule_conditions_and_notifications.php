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
            if (! Schema::hasColumn('loyalty_rules', 'min_hours')) {
                $table->decimal('min_hours', 10, 4)->nullable()->after('trigger_type');
            }

            if (! Schema::hasColumn('loyalty_rules', 'period_months')) {
                $table->unsignedInteger('period_months')->nullable()->after('min_hours');
            }

            if (! Schema::hasColumn('loyalty_rules', 'min_subscription_months')) {
                $table->unsignedInteger('min_subscription_months')->nullable()->after('period_months');
            }

            if (! Schema::hasColumn('loyalty_rules', 'min_visits')) {
                $table->unsignedInteger('min_visits')->nullable()->after('min_subscription_months');
            }
        });

        if (Schema::hasColumn('loyalty_rules', 'condition_json')) {
            DB::table('loyalty_rules')
                ->whereNotNull('condition_json')
                ->orderBy('id')
                ->get(['id', 'condition_json'])
                ->each(function ($rule): void {
                    $conditions = json_decode((string) $rule->condition_json, true) ?: [];

                    DB::table('loyalty_rules')
                        ->where('id', $rule->id)
                        ->update([
                            'min_hours' => $conditions['min_hours'] ?? null,
                            'period_months' => $conditions['period_months'] ?? null,
                            'min_subscription_months' => $conditions['min_subscription_months'] ?? null,
                            'min_visits' => $conditions['min_visits'] ?? null,
                        ]);
                });

            Schema::table('loyalty_rules', function (Blueprint $table) {
                $table->dropColumn('condition_json');
            });
        }

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loyalty_rules', function (Blueprint $table) {
            if (! Schema::hasColumn('loyalty_rules', 'condition_json')) {
                $table->json('condition_json')->nullable()->after('trigger_type');
            }
        });

        DB::table('loyalty_rules')
            ->orderBy('id')
            ->get(['id', 'min_hours', 'period_months', 'min_subscription_months', 'min_visits'])
            ->each(function ($rule): void {
                DB::table('loyalty_rules')
                    ->where('id', $rule->id)
                    ->update([
                        'condition_json' => json_encode(array_filter([
                            'min_hours' => $rule->min_hours,
                            'period_months' => $rule->period_months,
                            'min_subscription_months' => $rule->min_subscription_months,
                            'min_visits' => $rule->min_visits,
                        ], fn ($value) => $value !== null)),
                    ]);
            });

        Schema::table('loyalty_rules', function (Blueprint $table) {
            foreach (['min_hours', 'period_months', 'min_subscription_months', 'min_visits'] as $column) {
                if (Schema::hasColumn('loyalty_rules', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('notifications');
    }
};
