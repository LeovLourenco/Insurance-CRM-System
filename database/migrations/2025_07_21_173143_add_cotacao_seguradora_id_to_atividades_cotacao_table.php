<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCotacaoSeguradoraIdToAtividadesCotacaoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('atividades_cotacao', function (Blueprint $table) {
            // Adicionar campo para atividades específicas por seguradora
            $table->unsignedBigInteger('cotacao_seguradora_id')->nullable()->after('cotacao_id');
            
            // Foreign key para cotacao_seguradoras
            $table->foreign('cotacao_seguradora_id')
                  ->references('id')
                  ->on('cotacao_seguradoras')
                  ->onDelete('cascade');
            
            // Índice para performance
            $table->index(['cotacao_seguradora_id']);
            
            // Adicionar campo tipo para facilitar filtros (opcional)
            $table->enum('tipo', ['geral', 'seguradora'])->default('geral')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('atividades_cotacao', function (Blueprint $table) {
            $table->dropForeign(['cotacao_seguradora_id']);
            $table->dropColumn(['cotacao_seguradora_id', 'tipo']);
        });
    }
}