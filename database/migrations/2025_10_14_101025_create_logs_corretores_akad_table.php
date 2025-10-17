<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsCorretoresAkadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs_corretores_akad', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com corretor
            $table->foreignId('corretor_akad_id')->constrained('corretores_akad')->onDelete('cascade');
            
            // Tipo de evento
            $table->enum('evento', [
                'cadastro_criado',         // Corretor se cadastrou
                'dados_atualizados',       // Dados do corretor foram alterados
                'documento_enviado',       // Documento enviado via Autentique
                'documento_assinado',      // Documento foi assinado
                'documento_recusado',      // Documento foi recusado
                'documento_expirado',      // Documento expirou sem assinatura
                'documento_cancelado',     // Documento foi cancelado
                'status_alterado',         // Status do corretor foi alterado
                'corretor_ativado',        // Corretor foi ativado no sistema
                'corretor_desativado',     // Corretor foi desativado
                'erro_api_autentique',     // Erro na comunicação com Autentique
                'tentativa_reenvio'        // Tentativa de reenvio de documento
            ]);
            
            // Detalhes do evento
            $table->text('descricao'); // Descrição detalhada do evento
            $table->json('dados_anteriores')->nullable(); // Estado anterior (para alterações)
            $table->json('dados_novos')->nullable(); // Novo estado (para alterações)
            $table->json('dados_extras')->nullable(); // Dados adicionais (resposta API, etc)
            
            // Metadados
            $table->string('ip_address', 45)->nullable(); // IP do usuário/sistema
            $table->text('user_agent')->nullable(); // User agent (se aplicável)
            $table->string('origem')->default('sistema'); // 'sistema', 'webhook', 'manual'
            
            // IDs de referência externa
            $table->string('documento_id')->nullable(); // ID do documento no Autentique
            $table->string('evento_externo_id')->nullable(); // ID do evento no sistema externo
            
            $table->timestamps();
            
            // Índices para performance
            $table->index(['corretor_akad_id', 'evento']);
            $table->index(['evento']);
            $table->index(['created_at']);
            $table->index(['documento_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs_corretores_akad');
    }
}
