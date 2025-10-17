<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorretoresAkadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corretores_akad', function (Blueprint $table) {
            $table->id();
            
            // Dados pessoais
            $table->string('nome');
            $table->string('email')->unique();
            $table->string('cpf', 14)->unique(); // Com formatação XXX.XXX.XXX-XX
            $table->string('creci', 20);
            $table->string('estado', 2); // UF
            $table->string('telefone', 20);
            
            // Status do cadastro
            $table->enum('status', [
                'pendente',           // Recém cadastrado
                'documento_enviado',  // Documento enviado via Autentique
                'assinado',          // Documento assinado
                'recusado',          // Documento recusado
                'ativo',             // Corretor ativo
                'inativo'            // Corretor inativo
            ])->default('pendente');
            
            // Metadados
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('documento_enviado_em')->nullable();
            $table->timestamp('assinado_em')->nullable();
            $table->timestamp('recusado_em')->nullable();
            $table->text('motivo_recusa')->nullable();
            
            $table->timestamps();
            
            // Índices para performance
            $table->index(['status']);
            $table->index(['estado']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('corretores_akad');
    }
}
