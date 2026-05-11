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
            // Stored in natural currency units (e.g. 100 for $100, 10000 for ¥10,000).
            // null means "use the system default for this currency".
            $table->unsignedInteger('high_spend_threshold')->nullable()->after('default_currency');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('high_spend_threshold');
        });
    }
};
