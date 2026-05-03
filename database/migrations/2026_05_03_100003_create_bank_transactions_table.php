<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->enum('source', ['manual', 'email', 'api'])->default('email');
            $table->foreignId('source_message_id')->nullable()->constrained('bank_email_messages')->nullOnDelete();
            $table->string('bank_name')->nullable();
            $table->enum('transaction_type', ['debit', 'credit', 'unknown'])->default('unknown');
            $table->string('masked_card_number')->nullable();
            $table->string('masked_account_number')->nullable();
            $table->string('description')->nullable();
            $table->decimal('amount', 15, 3)->default(0);
            $table->string('currency', 10)->default('OMR');
            $table->decimal('balance_after', 15, 3)->nullable();
            $table->string('transaction_country')->nullable();
            $table->timestamp('transaction_datetime')->nullable();
            $table->enum('status', ['pending_review', 'confirmed', 'ignored'])->default('pending_review');
            $table->foreignId('matched_purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
            $table->foreignId('matched_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('chart_of_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->string('dedupe_hash', 64)->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique('dedupe_hash');
            $table->index(['status', 'transaction_type']);
            $table->index('transaction_datetime');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
