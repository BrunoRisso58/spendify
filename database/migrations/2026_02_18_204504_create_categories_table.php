<?php

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    use SoftDeletes;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['entrada', 'saída'])->default('saída');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->unique(['name', 'user_id']);
            $table->index('type');
            $table->index('user_id');

            $table->softDeletes();
        });

        $this->seed($this->getCategories());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }

    public function seed($categories)
    {
        DB::table('categories')->insert($categories);
    }

    public function getCategories(): array
    {
        return [
            // Entradas
            ['name' => 'Salário', 'type' => 'entrada'],
            ['name' => 'Freelance / Serviços', 'type' => 'entrada'],
            ['name' => 'Comissões', 'type' => 'entrada'],
            ['name' => 'Dividendos / Juros', 'type' => 'entrada'],
            ['name' => 'Transferências recebidas', 'type' => 'entrada'],
            ['name' => 'Presentes em dinheiro', 'type' => 'entrada'],
            ['name' => 'Venda de itens', 'type' => 'entrada'],
            ['name' => 'Outros (entrada)', 'type' => 'entrada'],

            // Saídas
            ['name' => 'Casa', 'type' => 'saida'],
            ['name' => 'Supermercado', 'type' => 'saida'],
            ['name' => 'Restaurantes', 'type' => 'saida'],
            ['name' => 'Transporte', 'type' => 'saida'],
            ['name' => 'Saúde', 'type' => 'saida'],
            ['name' => 'Viagens', 'type' => 'saida'],
            ['name' => 'Streaming', 'type' => 'saida'],
            ['name' => 'Lazer', 'type' => 'saida'],
            ['name' => 'Trabalho', 'type' => 'saida'],
            ['name' => 'Academia', 'type' => 'saida'],
            ['name' => 'Cartão de crédito (outros)', 'type' => 'saida'],
            ['name' => 'Empréstimos', 'type' => 'saida'],
            ['name' => 'Impostos', 'type' => 'saida'],
            ['name' => 'Investimentos', 'type' => 'saida'],
            ['name' => 'Doações / Ofertas', 'type' => 'saida'],
            ['name' => 'Roupas', 'type' => 'saida'],
            ['name' => 'Compras diversas', 'type' => 'saida'],
            ['name' => 'Outros (saída)', 'type' => 'saida'],
        ];
    }
};
