<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSeguradoIdToCotacaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cotacaos', function (Blueprint $table) {
            $table->foreignId('segurado_id')
              ->nullable()
              ->constrained('segurados')
              ->onDelete('set null')
              ->after('seguradora_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cotacaos', function (Blueprint $table) {
            //
        });
    }
}
