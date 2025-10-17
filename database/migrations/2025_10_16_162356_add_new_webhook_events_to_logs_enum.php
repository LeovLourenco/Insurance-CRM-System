<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddNewWebhookEventsToLogsEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Adicionar novos eventos webhook ao ENUM
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remover novos eventos webhook do ENUM
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
