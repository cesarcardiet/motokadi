<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('credit_profile', 40)->nullable()->after('national_id');
            $table->boolean('is_reference_profile')->default(false)->after('credit_profile');
        });

        Schema::create('financing_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_application_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('motorcycle_id')->constrained()->restrictOnDelete();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->string('contract_number')->unique();
            $table->string('status')->default('activo');
            $table->timestamp('signed_at');
            $table->date('start_date');
            $table->unsignedTinyInteger('term_months');
            $table->decimal('monthly_payment', 12, 2);
            $table->decimal('balance', 12, 2);
            $table->timestamps();
        });

        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financing_contract_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('number');
            $table->date('due_date');
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('pendiente');
            $table->timestamp('paid_at')->nullable();
            $table->unsignedSmallInteger('days_late')->default(0);
            $table->decimal('late_fee', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['financing_contract_id', 'number']);
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installments');
        Schema::dropIfExists('financing_contracts');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['credit_profile', 'is_reference_profile']);
        });
    }
};
