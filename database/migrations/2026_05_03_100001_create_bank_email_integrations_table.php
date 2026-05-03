<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_email_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
            $table->string('parser_key')->default('generic');
            $table->string('email_address');
            $table->string('imap_host');
            $table->unsignedSmallInteger('imap_port')->default(993);
            $table->enum('encryption', ['ssl', 'tls', 'none'])->default('ssl');
            $table->boolean('validate_cert')->default(true);
            $table->string('username');
            $table->text('encrypted_password');
            $table->string('mailbox_folder')->default('INBOX');
            $table->string('sender_filter')->nullable();
            $table->string('keyword_filter')->nullable();
            $table->foreignId('linked_bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_confirm')->default(false);
            $table->boolean('mark_seen_after_import')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->text('last_sync_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_email_integrations');
    }
};
