<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->timestamp('rate_frozen_at')->nullable()->after('amount_ves');
        });

        DB::table('payment_vouchers')
            ->whereNull('rate_frozen_at')
            ->update([
                'rate_frozen_at' => DB::raw('created_at'),
            ]);
    }

    public function down(): void
    {
        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->dropColumn('rate_frozen_at');
        });
    }
};
