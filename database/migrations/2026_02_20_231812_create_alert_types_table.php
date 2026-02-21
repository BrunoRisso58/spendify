<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\AlertType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('alert_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        AlertType::insert($this->getTypes());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alert_types');
    }

    public function getTypes()
    {
        return [
            // BUDGET
            [
                'name' => 'budget_warning',
                'label' => 'Limite próximo',
                'description' => 'Atingiu ~80% do orçamento'
            ],
            [
                'name' => 'budget_exceeded',
                'label' => 'Orçamento excedido',
                'description' => 'Passou do limite definido'
            ],

            // SALDO
            [
                'name' => 'negative_balance',
                'label' => 'Saldo negativo',
                'description' => 'Conta ficou negativa'
            ],

            // RECORRÊNCIA
            [
                'name' => 'recurrence_due',
                'label' => 'Recorrência próxima',
                'description' => 'Uma transação recorrente ocorrerá em breve'
            ],
            [
                'name' => 'recurrence_failed',
                'label' => 'Falha na recorrência',
                'description' => 'Não foi possível gerar a transação recorrente'
            ],

            // CONTAS / VENCIMENTOS
            [
                'name' => 'bill_due_today',
                'label' => 'Conta vence hoje',
                'description' => 'Uma conta vence hoje'
            ],
            [
                'name' => 'bill_due_soon',
                'label' => 'Conta próxima do vencimento',
                'description' => 'Uma conta vencerá nos próximos dias'
            ],
        ];
    }
};
