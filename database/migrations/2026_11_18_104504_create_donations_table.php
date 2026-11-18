<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('donation_cause_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('reference')->unique();
            $table->string('donor_name', 160);
            $table->string('donor_email');
            $table->string('donor_phone', 24)->nullable();
            $table->unsignedBigInteger('amount_minor');
            $table->char('currency', 3);
            $table->string('status', 16)->default('pending')->index();
            $table->uuid('pesapal_tracking_id')->nullable()->unique();
            $table->text('checkout_url')->nullable();
            $table->json('payment_history')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('receipt_sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
