<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApolicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apolices', function (Blueprint $table) {
            $table->id();
            
            // Relação com cotação (opcional)
            $table->foreignId('cotacao_id')->nullable()->constrained('cotacoes')->onDelete('set null');
            
            // Identificação da apólice
            $table->string('numero_apolice')->nullable()->unique();
            $table->enum('status', ['pendente_emissao', 'ativa', 'renovacao', 'cancelada'])
                  ->default('pendente_emissao');
            
            // Dados do segurado/corretor (para apólices importadas)
            $table->string('nome_segurado')->nullable();
            $table->string('cnpj_segurado', 18)->nullable();
            $table->string('nome_corretor')->nullable();
            $table->string('cnpj_corretor', 18)->nullable();
            
            // Relacionamentos (nullable para importadas)
            $table->foreignId('seguradora_id')->nullable()->constrained('seguradoras')->onDelete('set null');
            $table->foreignId('segurado_id')->nullable()->constrained('segurados')->onDelete('set null');
            $table->foreignId('corretora_id')->nullable()->constrained('corretoras')->onDelete('set null');
            $table->foreignId('produto_id')->nullable()->constrained('produtos')->onDelete('set null');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Valores e datas
            $table->decimal('premio_liquido', 12, 2)->nullable();
            $table->date('data_emissao')->nullable();
            $table->date('inicio_vigencia')->nullable();
            $table->date('fim_vigencia')->nullable();
            
            // Campos específicos do Excel
            $table->string('endosso', 20)->nullable();
            $table->integer('parcela')->default(0);
            $table->integer('total_parcelas')->default(0);
            $table->string('ramo', 10)->nullable();
            $table->string('linha_produto')->nullable();
            $table->date('data_pagamento')->nullable();
            
            // Controle e matching
            $table->enum('origem', ['cotacao', 'importacao'])->default('cotacao');
            $table->string('metodo_matching', 50)->nullable();
            $table->integer('confianca_matching')->nullable();
            $table->text('observacoes_endosso')->nullable();
            $table->string('arquivo_sharepoint')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['status', 'fim_vigencia']);
            $table->index(['origem', 'created_at']);
            $table->index('cnpj_corretor');
            $table->index('cnpj_segurado');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('apolices');
    }
}
