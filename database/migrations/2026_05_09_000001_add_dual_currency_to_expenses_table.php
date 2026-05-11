<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedInteger('original_amount')->nullable()->after('amount');
            $table->string('original_currency', 3)->nullable()->after('original_amount');
            $table->unsignedInteger('base_amount')->nullable()->after('original_currency');
            $table->decimal('exchange_rate', 14, 6)->nullable()->after('base_amount');
        });

        // Backfill existing rows: no historical rate available — treat original as base (rate 1)
        DB::table('expenses')->update([
            'original_amount'   => DB::raw('`amount`'),
            'original_currency' => DB::raw('`currency`'),
            'base_amount'       => DB::raw('`amount`'),
            'exchange_rate'     => 1.000000,
        ]);
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['original_amount', 'original_currency', 'base_amount', 'exchange_rate']);
        });
    }
};
