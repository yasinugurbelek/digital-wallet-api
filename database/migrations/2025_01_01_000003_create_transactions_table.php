<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['deposit', 'withdrawal', 'transfer', 'refund']);
            $table->decimal('amount', 20, 2);
            $table->decimal('fee', 20, 2)->default(0);
            $table->decimal('balance_before', 20, 2);
            $table->decimal('balance_after', 20, 2);
            $table->enum('status', ['completed', 'pending', 'failed', 'pending_review'])->default('completed');
            $table->string('currency', 3);
            $table->foreignId('related_transaction_id')->nullable()->constrained('transactions');
            $table->foreignId('from_user_id')->nullable()->constrained('users');
            $table->foreignId('to_user_id')->nullable()->constrained('users');
            $table->string('idempotency_key')->nullable()->unique();
            $table->string('ip_address')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['wallet_id', 'created_at']);
            $table->index(['type', 'status']);
            $table->index('status');
            $table->index('created_at');
            $table->index('idempotency_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
