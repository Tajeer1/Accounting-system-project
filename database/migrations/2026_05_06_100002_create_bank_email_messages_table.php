<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_email_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gmail_account_id')->constrained('gmail_accounts')->cascadeOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->string('gmail_message_id')->unique();
            $table->string('thread_id')->nullable();
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->string('subject')->nullable();
            $table->text('snippet')->nullable();
            $table->longText('body_plain')->nullable();
            $table->longText('body_html')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->string('bank_key')->nullable();
            $table->enum('status', ['pending', 'parsed', 'ignored', 'failed'])->default('pending');
            $table->text('parse_error')->nullable();
            $table->timestamps();

            $table->index(['status', 'received_at']);
            $table->index('bank_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_email_messages');
    }
};
