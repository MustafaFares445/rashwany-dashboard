<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rewards')) {
            Schema::table('rewards', function (Blueprint $table) {
                if (! Schema::hasColumn('rewards', 'qualified_at')) {
                    $table->timestamp('qualified_at')->nullable()->index()->after('status');
                }

                if (! Schema::hasColumn('rewards', 'activated_by')) {
                    $table->foreignId('activated_by')->nullable()->after('granted_at')->constrained('users')->nullOnDelete();
                }
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

    public function down(): void
    {
        if (Schema::hasTable('rewards')) {
            Schema::table('rewards', function (Blueprint $table) {
                if (Schema::hasColumn('rewards', 'activated_by')) {
                    $table->dropConstrainedForeignId('activated_by');
                }

                if (Schema::hasColumn('rewards', 'qualified_at')) {
                    $table->dropColumn('qualified_at');
                }
            });
        }

        Schema::dropIfExists('notifications');
    }
};
