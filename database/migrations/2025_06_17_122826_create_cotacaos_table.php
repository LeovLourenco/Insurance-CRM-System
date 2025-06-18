<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCotacaosTable extends Migration
{
    public function up()
    {
        Schema::create('cotacaos', function (Blueprint $table) {
            $table->id();

            // FK para a corretora que solicitou a cotação
            $table->unsignedBigInteger('corretora_id');

            // FK para o produto que está sendo cotado
            $table->unsignedBigInteger('produto_id');

            // Campo para observações adicionais (pode ficar vazio)
            $table->text('observacoes')->nullable();

            // Status da cotação com valor padrão "aguardando"
            // Poderia ser ENUM no futuro, mas string está ok para começar
            $table->string('status')->default('aguardando'); 

            $table->timestamps();

            // Definindo as chaves estrangeiras com delete em cascade
            $table->foreign('corretora_id')->references('id')->on('corretoras')->onDelete('cascade');
            $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cotacaos');
    }
}
