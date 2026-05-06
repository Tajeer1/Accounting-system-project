<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_email_message_id')->nullable()->constrained('bank_email_messages')->nullOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->enum('direction', ['debit', 'credit']);
            $table->decimal('amount', 15, 3);
            $table->string('currency', 8)->default('SAR');
            $table->date('transaction_date');
            $table->string('merchant')->nullable();
            $table->string('reference')->nullable();
            $table->string('card_last4', 8)->nullable();
            $table->decimal('balance_after', 15, 3)->nullable();
            $table->text('raw_match')->nullable();
            $table->enum('status', ['pending_review', 'approved', 'linked', 'rejected'])->default('pending_review');
            $table->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['bank_account_id', 'reference', 'transaction_date', 'amount'], 'bank_tx_dedupe');
            $table->index(['status', 'transaction_date']);
            $table->index('direction');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
