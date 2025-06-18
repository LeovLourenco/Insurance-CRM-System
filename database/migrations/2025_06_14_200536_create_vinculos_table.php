<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVinculosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vinculos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('corretora_id');
            $table->unsignedBigInteger('seguradora_id');
            $table->unsignedBigInteger('produto_id');

            $table->string('canal')->nullable(); // Ex: e-mail, portal, whatsapp
            $table->text('observacoes')->nullable();

            $table->timestamps();
            // Chaves estrangeiras (FKs)
        $table->foreign('corretora_id')->references('id')->on('corretoras')->onDelete('cascade');
        $table->foreign('seguradora_id')->references('id')->on('seguradoras')->onDelete('cascade');
        $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('cascade');

        // Impedir duplicações exatas
        $table->unique(['corretora_id', 'seguradora_id', 'produto_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vinculos');
    }
}
