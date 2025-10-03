<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCadastrosCorretoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cadastros_corretores', function (Blueprint $table) {
            $table->id();
            $table->datetime('data_hora');
            $table->string('corretora');
            $table->string('cnpj', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('responsavel')->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('seguradoras')->nullable();
            $table->string('tipo', 50)->nullable();
            $table->timestamps();
            
            // Índices para performance
            $table->index(['data_hora']);
            $table->index(['corretora']);
            $table->index(['cnpj']);
            $table->index(['email']);
            $table->index(['tipo']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cadastros_corretores');
    }
}
