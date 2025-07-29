<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveSeguradoraIdFromCotacoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cotacoes', function (Blueprint $table) {
            // 1º - Remove a foreign key constraint
            $table->dropForeign('cotacaos_seguradora_id_foreign');
            // 2º - Remove a coluna
            $table->dropColumn('seguradora_id');
        });
    }

    public function down()
    {
        Schema::table('cotacoes', function (Blueprint $table) {
            $table->unsignedBigInteger('seguradora_id');
            $table->foreign('seguradora_id', 'cotacaos_seguradora_id_foreign')
                ->references('id')->on('seguradoras');
        });
    }
}