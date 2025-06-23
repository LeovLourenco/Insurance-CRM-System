<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSeguradoraIdToCotacoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('cotacaos', function (Blueprint $table) {
        $table->unsignedBigInteger('seguradora_id')->nullable();

        $table->foreign('seguradora_id')->references('id')->on('seguradoras');
    });
}


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cotacoes', function (Blueprint $table) {
            //
        });
    }
}
