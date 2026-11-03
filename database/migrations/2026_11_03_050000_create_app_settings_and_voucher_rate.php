<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        DB::table('app_settings')->insert([
            'key' => 'usd_ves_rate',
            'value' => '1000.00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->decimal('exchange_rate', 14, 4)->nullable()->after('amount');
            $table->decimal('amount_ves', 14, 2)->nullable()->after('exchange_rate');
        });
    }

    public function down(): void
    {
        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->dropColumn(['exchange_rate', 'amount_ves']);
        });

        Schema::dropIfExists('app_settings');
    }
};
