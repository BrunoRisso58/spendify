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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('category_id')->nullable();

            $table->string('title');

            $table->decimal('amount', 12, 2);
            $table->enum('type', ['entrada', 'saída']);

            $table->date('date');
            $table->string('description')->nullable();

            $table->unsignedBigInteger('recurrence_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('recurrence_id')->references('id')->on('recurrences')->nullOnDelete();

            $table->index(['user_id', 'date']);
            $table->index('type');

            $table->unique(['recurrence_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
