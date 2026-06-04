<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('credit_installments')) {
            return;
        }

        Schema::create('credit_installments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_batch_id')->constrained('product_batches')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 14, 2)->default(0);
            $table->date('paid_at')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['product_batch_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_installments');
    }
};

