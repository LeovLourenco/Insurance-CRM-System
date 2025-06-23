<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAtividadesCotacaoTable extends Migration
{
    public function up()
    {
        Schema::create('atividades_cotacao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotacao_id')->constrained('cotacaos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('descricao');
            $table->timestamps();

            // Define o engine e charset diretamente
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });
    }

    public function down()
    {
        Schema::dropIfExists('atividades_cotacao');
    }
}
