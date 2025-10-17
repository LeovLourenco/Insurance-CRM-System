<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Modificar corretores_akad - remover unique do email
        if (Schema::hasTable('corretores_akad')) {
            try {
                DB::statement('ALTER TABLE corretores_akad DROP INDEX corretores_akad_email_unique');
            } catch (\Exception $e) {
                // Constraint pode não existir
            }
        }

        // 2. Adicionar campos webhook em documentos_akad
        if (Schema::hasTable('documentos_akad')) {
            Schema::table('documentos_akad', function (Blueprint $table) {
                if (!Schema::hasColumn('documentos_akad', 'data_assinatura')) {
                    $table->timestamp('data_assinatura')->nullable()->after('status');
                }
                if (!Schema::hasColumn('documentos_akad', 'dados_assinatura')) {
                    $table->json('dados_assinatura')->nullable()->after('data_assinatura');
                }
                if (!Schema::hasColumn('documentos_akad', 'motivo_rejeicao')) {
                    $table->string('motivo_rejeicao')->nullable()->after('dados_assinatura');
                }
                if (!Schema::hasColumn('documentos_akad', 'dados_rejeicao')) {
                    $table->json('dados_rejeicao')->nullable()->after('motivo_rejeicao');
                }
                if (!Schema::hasColumn('documentos_akad', 'finalizado_em')) {
                    $table->timestamp('finalizado_em')->nullable()->after('dados_rejeicao');
                }
                if (!Schema::hasColumn('documentos_akad', 'dados_finalizacao')) {
                    $table->json('dados_finalizacao')->nullable()->after('finalizado_em');
                }
                if (!Schema::hasColumn('documentos_akad', 'dados_expiracao')) {
                    $table->json('dados_expiracao')->nullable();
                }
            });
        }

        // 3. Atualizar ENUM de logs_corretores_akad
        if (Schema::hasTable('logs_corretores_akad')) {
            DB::statement("ALTER TABLE logs_corretores_akad MODIFY evento ENUM(
                'cadastro_criado',
                'dados_atualizados',
                'documento_enviado',
                'documento_assinado',
                'documento_recusado',
                'documento_expirado',
                'documento_cancelado',
                'status_alterado',
                'corretor_ativado',
                'corretor_desativado',
                'erro_api_autentique',
                'tentativa_reenvio',
                'documento_visualizado',
                'documento_finalizado'
            )");
        }
    }

    public function down()
    {
        // Rollback das alterações
        
        // 1. Restaurar unique do email
        if (Schema::hasTable('corretores_akad')) {
            try {
                DB::statement('ALTER TABLE corretores_akad ADD UNIQUE KEY corretores_akad_email_unique (email)');
            } catch (\Exception $e) {
                // Pode já existir
            }
        }

        // 2. Remover campos webhook de documentos_akad
        if (Schema::hasTable('documentos_akad')) {
            Schema::table('documentos_akad', function (Blueprint $table) {
                $columns = [
                    'data_assinatura',
                    'dados_assinatura',
                    'motivo_rejeicao',
                    'dados_rejeicao',
                    'finalizado_em',
                    'dados_finalizacao',
                    'dados_expiracao'
                ];
                
                foreach ($columns as $column) {
                    if (Schema::hasColumn('documentos_akad', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        // 3. Restaurar ENUM original
        if (Schema::hasTable('logs_corretores_akad')) {
            DB::statement("ALTER TABLE logs_corretores_akad MODIFY evento ENUM(
                'cadastro_criado',
                'dados_atualizados',
                'documento_enviado',
                'documento_assinado',
                'documento_recusado',
                'documento_expirado',
                'documento_cancelado',
                'status_alterado',
                'corretor_ativado',
                'corretor_desativado',
                'erro_api_autentique',
                'tentativa_reenvio'
            )");
        }
    }
};