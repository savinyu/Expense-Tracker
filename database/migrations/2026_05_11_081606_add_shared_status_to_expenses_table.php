<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // 'personal' | 'pending' | 'shared'
            // Default 'personal' so existing rows are untouched semantically.
            $table->string('shared_status', 16)
                ->default('personal')
                ->after('description')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['shared_status']);
            $table->dropColumn('shared_status');
        });
    }
};
