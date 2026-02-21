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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('type');

            $table->unsignedBigInteger('reference_id')->nullable(); 
            // ID do budget, recurrence, etc

            $table->string('period')->nullable(); 
            // Ex: 2026-02 (pra evitar duplicação mensal)

            $table->date('date')->nullable(); 
            // Ex: data específica do evento

            $table->boolean('read')->default(false);

            $table->timestamps();

            $table->unique([
                'user_id',
                'type',
                'reference_id',
                'period',
                'date'
            ], 'alerts_unique_constraint');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
