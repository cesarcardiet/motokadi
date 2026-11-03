<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financing_contracts', function (Blueprint $table) {
            $table->string('unit_cartel', 80)->nullable()->after('balance');
            $table->string('unit_plate', 80)->nullable()->after('unit_cartel');
            $table->string('unit_serial', 120)->nullable()->after('unit_plate');
            $table->timestamp('unit_assigned_at')->nullable()->after('unit_serial');
        });
    }

    public function down(): void
    {
        Schema::table('financing_contracts', function (Blueprint $table) {
            $table->dropColumn(['unit_cartel', 'unit_plate', 'unit_serial', 'unit_assigned_at']);
        });
    }
};
