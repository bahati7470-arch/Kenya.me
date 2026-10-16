<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('quote_number', 24)->unique();
            $table->text('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('markup_percent', 5, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('amount', 12, 2);
            $table->string('status', 24)->default('sent')->index();
            $table->date('valid_until')->nullable();
            $table->timestamps();
        });

        Schema::create('mpesa_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('receipt_number', 32)->unique();
            $table->decimal('amount', 12, 2);
            $table->string('phone', 32)->nullable()->index();
            $table->string('bill_ref_number', 64)->nullable()->index();
            $table->timestamp('transacted_at')->nullable()->index();
            $table->string('status', 24)->default('unmatched')->index();
            $table->string('match_method', 32)->nullable();
            $table->decimal('match_confidence', 5, 2)->nullable();
            $table->json('callback_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_match_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mpesa_payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('strategy', 32);
            $table->decimal('confidence', 5, 2);
            $table->string('outcome', 24);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_match_attempts');
        Schema::dropIfExists('mpesa_payments');
        Schema::dropIfExists('quotes');
    }
};