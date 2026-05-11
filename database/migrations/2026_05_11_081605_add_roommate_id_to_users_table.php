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
        Schema::table('users', function (Blueprint $table) {
            // 1-to-1 roommate connection. Nullable since most users are solo.
            // On delete: set null (don't cascade — losing one user shouldn't delete the other).
            $table->foreignId('roommate_id')
                ->nullable()
                ->after('high_spend_threshold')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('roommate_id');
        });
    }
};
