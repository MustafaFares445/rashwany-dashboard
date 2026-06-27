<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('loyalty_rules')) {
            return;
        }

        Schema::table('loyalty_rules', function (Blueprint $table) {
            if (! Schema::hasColumn('loyalty_rules', 'min_total_hours')) {
                $table->decimal('min_total_hours', 10, 2)->nullable()->after('trigger_type');
            }

            if (! Schema::hasColumn('loyalty_rules', 'period_months')) {
                $table->unsignedSmallInteger('period_months')->nullable()->after('min_total_hours');
            }

            if (! Schema::hasColumn('loyalty_rules', 'min_subscription_months')) {
                $table->unsignedSmallInteger('min_subscription_months')->nullable()->after('period_months');
            }

            if (! Schema::hasColumn('loyalty_rules', 'min_visit_count')) {
                $table->unsignedInteger('min_visit_count')->nullable()->after('min_subscription_months');
            }
        });

        $this->backfillConditionColumns();

        if (Schema::hasColumn('loyalty_rules', 'condition_json')) {
            Schema::table('loyalty_rules', function (Blueprint $table) {
                $table->dropColumn('condition_json');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('loyalty_rules')) {
            return;
        }

        if (! Schema::hasColumn('loyalty_rules', 'condition_json')) {
            Schema::table('loyalty_rules', function (Blueprint $table) {
                $table->json('condition_json')->nullable()->after('trigger_type');
            });
        }

        $columns = array_values(array_filter([
            'min_total_hours',
            'period_months',
            'min_subscription_months',
            'min_visit_count',
        ], fn (string $column): bool => Schema::hasColumn('loyalty_rules', $column)));

        if ($columns !== []) {
            Schema::table('loyalty_rules', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }

    private function backfillConditionColumns(): void
    {
        if (! Schema::hasColumn('loyalty_rules', 'condition_json')) {
            return;
        }

        DB::table('loyalty_rules')
            ->whereNotNull('condition_json')
            ->orderBy('id')
            ->each(function (object $rule): void {
                $conditions = json_decode((string) $rule->condition_json, true);

                if (! is_array($conditions)) {
                    return;
                }

                DB::table('loyalty_rules')
                    ->where('id', $rule->id)
                    ->update([
                        'min_total_hours' => $conditions['min_hours'] ?? $conditions['min_total_hours'] ?? null,
                        'period_months' => $conditions['period_months'] ?? null,
                        'min_subscription_months' => $conditions['min_subscription_months'] ?? null,
                        'min_visit_count' => $conditions['min_visit_count'] ?? null,
                    ]);
            });
    }
};
