<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropVinculosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Verificar se a tabela existe antes de dropar
        if (Schema::hasTable('vinculos')) {
            Schema::dropIfExists('vinculos');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Não vamos recriar, pois não faz mais sentido no sistema
        // Se precisar de rollback, implementar estrutura básica aqui
    }
}