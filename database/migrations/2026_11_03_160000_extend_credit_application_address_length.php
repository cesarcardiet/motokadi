<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credit_applications', function (Blueprint $table) {
            $table->string('address', 500)->change();
        });
    }

    public function down(): void
    {
        Schema::table('credit_applications', function (Blueprint $table) {
            $table->string('address', 255)->change();
        });
    }
};
