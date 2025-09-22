<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReorganizeEmailColumnsInCorretorasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('corretoras', function (Blueprint $table) {
            // 1. Primeiro, aumentar o tamanho da coluna email para 500 caracteres
            $table->string('email', 500)->nullable()->change();
        });
        
        // 2. Copiar dados de email1 para email (se email estiver vazio), truncando se necessário
        \DB::statement("UPDATE corretoras SET email = LEFT(email1, 500) WHERE email IS NULL OR email = ''");
        
        Schema::table('corretoras', function (Blueprint $table) {
            // 3. Remove a coluna email1 já que os dados foram migrados para email
            $table->dropColumn('email1');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corretoras', function (Blueprint $table) {
            // Reverter: criar email1 e mover dados de volta
            $table->string('email1', 500)->nullable()->after('email');
        });
        
        // Mover dados de volta
        \DB::statement("UPDATE corretoras SET email1 = email");
        
        Schema::table('corretoras', function (Blueprint $table) {
            // Limpar coluna email original
            $table->string('email', 191)->nullable()->change();
        });
        
        \DB::statement("UPDATE corretoras SET email = NULL");
    }
}
