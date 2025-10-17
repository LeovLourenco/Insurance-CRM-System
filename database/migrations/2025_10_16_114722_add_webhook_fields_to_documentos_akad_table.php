<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWebhookFieldsToDocumentosAkadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documentos_akad', function (Blueprint $table) {
            $table->timestamp('data_assinatura')->nullable()->after('status');
            $table->json('dados_assinatura')->nullable()->after('data_assinatura');
            $table->string('motivo_rejeicao')->nullable()->after('dados_assinatura');
            $table->json('dados_rejeicao')->nullable()->after('motivo_rejeicao');
            $table->timestamp('finalizado_em')->nullable()->after('dados_rejeicao');
            $table->json('dados_finalizacao')->nullable()->after('finalizado_em');
            $table->json('dados_expiracao')->nullable()->after('expirado_em');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documentos_akad', function (Blueprint $table) {
            $table->dropColumn([
                'data_assinatura',
                'dados_assinatura',
                'motivo_rejeicao',
                'dados_rejeicao',
                'finalizado_em',
                'dados_finalizacao',
                'dados_expiracao'
            ]);
        });
    }
}
