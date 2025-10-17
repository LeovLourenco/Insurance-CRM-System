<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefactorCorretoresAkadSimplificarCampos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('corretores_akad', function (Blueprint $table) {
            // Adicionar novos campos necessários para FORM003
            $table->string('razao_social')->after('nome')->comment('Nome da corretora/empresa');
            $table->string('cnpj')->nullable()->after('razao_social')->comment('CNPJ da corretora');
            $table->string('codigo_susep')->nullable()->after('cnpj')->comment('Código SUSEP da corretora');
            
            // Tornar campos antigos nullable (não mais obrigatórios)
            $table->string('cpf')->nullable()->change();
            $table->string('creci')->nullable()->change();
            $table->string('estado', 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corretores_akad', function (Blueprint $table) {
            // Remover novos campos
            $table->dropColumn(['razao_social', 'cnpj', 'codigo_susep']);
            
            // Restaurar campos como obrigatórios (se necessário)
            $table->string('cpf')->nullable(false)->change();
            $table->string('creci')->nullable(false)->change();
            $table->string('estado', 2)->nullable(false)->change();
        });
    }
}
