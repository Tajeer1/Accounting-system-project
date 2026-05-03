<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_email_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_id')->constrained('bank_email_integrations')->cascadeOnDelete();
            $table->string('message_uid')->nullable();
            $table->string('message_id')->nullable();
            $table->string('sender')->nullable();
            $table->string('subject')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->longText('raw_body')->nullable();
            $table->enum('processing_status', ['pending', 'processed', 'failed', 'duplicate'])->default('pending');
            $table->text('error_message')->nullable();
            $table->string('content_hash', 64)->nullable();
            $table->timestamps();

            $table->unique(['integration_id', 'message_uid']);
            $table->unique(['integration_id', 'content_hash']);
            $table->index(['integration_id', 'processing_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_email_messages');
    }
};
