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
        Schema::table('atividades_cotacao', function (Blueprint $table) {
            // ✅ 1. Expandir descrição de VARCHAR(191) para TEXT
            $table->text('descricao')->change();
            
            // ✅ 2. Campos para status APROVADA
            $table->decimal('valor_premio', 10, 2)->nullable()->after('descricao');
            $table->decimal('valor_is', 10, 2)->nullable()->after('valor_premio');
            $table->text('condicoes')->nullable()->after('valor_is');
            
            // ✅ 3. Campos para status REJEITADA  
            $table->enum('motivo_rejeicao', [
                'perfil_nao_aceito',
                'valor_alto', 
                'documentacao',
                'prazo_vencido',
                'cliente_desistiu',
                'outro'
            ])->nullable()->after('condicoes');
            
            $table->string('motivo_outro')->nullable()->after('motivo_rejeicao');
            
            // ✅ 4. Campos para status REPIQUE
            $table->text('detalhes_repique')->nullable()->after('motivo_outro');
            $table->json('solicitacoes_repique')->nullable()->after('detalhes_repique');
            
            // ✅ 5. Campos para status EM_ANALISE
            $table->enum('prazo_resposta', ['24h', '48h', '72h', '1_semana'])->nullable()->after('solicitacoes_repique');
            
            // ✅ 6. Campos gerais
            $table->datetime('data_ocorrencia')->nullable()->after('prazo_resposta');
            $table->json('dados_extras')->nullable()->after('data_ocorrencia');
        });
        
        // ✅ 7. Expandir enum 'tipo' (ADD valores, não substituir)
        Schema::table('atividades_cotacao', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
        
        Schema::table('atividades_cotacao', function (Blueprint $table) {
            $table->enum('tipo', [
                'geral', 
                'seguradora', 
                'envio', 
                'observacao',
                'status_change',
                'sistema'
            ])->default('geral')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atividades_cotacao', function (Blueprint $table) {
            // Remover todos os campos adicionados
            $table->dropColumn([
                'valor_premio',
                'valor_is', 
                'condicoes',
                'motivo_rejeicao',
                'motivo_outro',
                'detalhes_repique',
                'solicitacoes_repique',
                'prazo_resposta',
                'data_ocorrencia',
                'dados_extras'
            ]);
            
            // Reverter descrição
            $table->string('descricao', 191)->change();
            
            // Reverter enum tipo
            $table->dropColumn('tipo');
        });
        
        Schema::table('atividades_cotacao', function (Blueprint $table) {
            $table->enum('tipo', ['geral', 'seguradora'])->default('geral')->after('user_id');
        });
    }
};