<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentosAkadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documentos_akad', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com corretor
            $table->foreignId('corretor_akad_id')->constrained('corretores_akad')->onDelete('cascade');
            
            // Dados do documento no Autentique
            $table->string('documento_id')->unique(); // ID retornado pelo Autentique
            $table->string('nome_documento')->default('Contrato Corretor AKAD');
            $table->text('link_assinatura'); // URL para assinatura
            
            // Status do documento
            $table->enum('status', [
                'enviado',    // Enviado para assinatura
                'assinado',   // Assinado pelo corretor
                'recusado',   // Recusado pelo corretor
                'expirado',   // Documento expirado
                'cancelado'   // Cancelado pelo sistema
            ])->default('enviado');
            
            // Timestamps de eventos
            $table->timestamp('assinado_em')->nullable();
            $table->timestamp('recusado_em')->nullable();
            $table->timestamp('expirado_em')->nullable();
            $table->text('motivo_recusa')->nullable();
            
            // Dados adicionais do Autentique
            $table->json('dados_autentique')->nullable(); // Response completa da API
            $table->string('token_autentique')->nullable(); // Token específico do documento
            
            $table->timestamps();
            
            // Índices para performance
            $table->index(['status']);
            $table->index(['documento_id']);
            $table->index(['corretor_akad_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documentos_akad');
    }
}
