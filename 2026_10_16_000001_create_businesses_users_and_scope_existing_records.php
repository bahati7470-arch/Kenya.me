<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 160);
            $table->string('phone', 32)->nullable();
            $table->string('email')->nullable();
            $table->string('paybill_shortcode', 32)->nullable()->unique();
            $table->string('webhook_token', 64)->nullable()->unique();
            $table->text('daraja_consumer_key')->nullable();
            $table->text('daraja_consumer_secret')->nullable();
            $table->timestamp('daraja_registered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name', 160);
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role', 24)->default('owner');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->foreignId('business_id')->nullable()->constrained()->cascadeOnDelete();
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->foreignId('business_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('business_id');
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('business_id');
        });

        Schema::dropIfExists('users');
        Schema::dropIfExists('businesses');
    }
};