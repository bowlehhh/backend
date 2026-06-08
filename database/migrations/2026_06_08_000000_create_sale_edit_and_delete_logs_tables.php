<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sale_edit_logs')) {
            Schema::create('sale_edit_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('sale_id')->constrained('sales')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreignId('edited_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('invoice_number')->index();
                $table->longText('old_data')->nullable();
                $table->longText('new_data')->nullable();
                $table->text('changed_fields')->nullable();
                $table->string('edit_note', 1000)->nullable();
                $table->timestamps();

                $table->index(['edited_by_user_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('sale_delete_logs')) {
            Schema::create('sale_delete_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
                $table->string('invoice_number')->index();
                $table->foreignId('deleted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('payment_method', 50)->nullable();
                $table->decimal('total', 14, 2)->default(0);
                $table->decimal('credit_amount', 14, 2)->default(0);
                $table->unsignedInteger('items_count')->default(0);
                $table->string('delete_note', 1000)->nullable();
                $table->json('snapshot')->nullable();
                $table->timestamps();

                $table->index(['deleted_by_user_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_delete_logs');
        Schema::dropIfExists('sale_edit_logs');
    }
};

