<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCotacaoSeguradorasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cotacao_seguradoras', function (Blueprint $table) {
            $table->id();
            
            // FK para a cotação (cabeçalho)
            $table->unsignedBigInteger('cotacao_id');
            
            // FK para a seguradora específica
            $table->unsignedBigInteger('seguradora_id');
            
            // Status individual por seguradora
            $table->string('status')->default('aguardando');
            
            // Observações específicas da seguradora
            $table->text('observacoes')->nullable();
            
            // Controle de datas
            $table->datetime('data_envio')->nullable();
            $table->datetime('data_retorno')->nullable();
            
            // Valores de retorno (opcional para futuro)
            $table->decimal('valor_premio', 10, 2)->nullable();
            $table->decimal('valor_is', 10, 2)->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('cotacao_id')->references('id')->on('cotacoes')->onDelete('cascade');
            $table->foreign('seguradora_id')->references('id')->on('seguradoras')->onDelete('cascade');
            
            // Índices para performance
            $table->index(['cotacao_id', 'seguradora_id']);
            $table->index(['status']);
            $table->index(['data_envio']);
            
            // Evitar duplicatas (mesma cotação + mesma seguradora)
            $table->unique(['cotacao_id', 'seguradora_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cotacao_seguradoras');
    }
}