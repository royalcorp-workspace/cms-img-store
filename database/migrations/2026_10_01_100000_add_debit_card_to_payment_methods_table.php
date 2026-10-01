<?php

use App\Models\PaymentMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('payment_methods')) {
            PaymentMethod::withoutGlobalScopes()->updateOrCreate(
                ['code' => 'DEBITCARD'],
                [
                    'name' => 'Kartu Debit (Debit Online)',
                    'type' => 6,
                    'provider' => 'espay',
                    'image' => 'https://img.icons8.com/color/48/bank-card-back-side.png',
                    'has_charge' => false,
                    'minimum_amount' => 10000,
                    'sort_order' => 7,
                    'status' => 1,
                    'deleted' => false,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('payment_methods')) {
            PaymentMethod::withoutGlobalScopes()->where('code', 'DEBITCARD')->delete();
        }
    }
};
