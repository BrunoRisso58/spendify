<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('amount', 12, 2);

            $table->enum('period', ['weekly', 'monthly', 'yearly']);

            $table->date('start_date');
            $table->date('end_date')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'period']);
            $table->index(['user_id', 'category_id']);

            $table->unique([
                'user_id',
                'category_id',
                'period',
                'start_date'
            ], 'budgets_unique_scope');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
