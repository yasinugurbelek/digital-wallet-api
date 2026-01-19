<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('currency', ['TRY', 'USD', 'EUR']);
            $table->decimal('balance', 20, 2)->default(0);
            $table->boolean('is_blocked')->default(false);
            $table->string('block_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['user_id', 'currency']);
            $table->index(['user_id', 'currency']);
            $table->index('is_blocked');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
