<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalColumnsToCorretorasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('corretoras', function (Blueprint $table) {
            $table->string('suc_cpd')->nullable()->after('telefone');
            $table->string('estado')->nullable()->after('suc_cpd');
            $table->string('cidade')->nullable()->after('estado');
            $table->string('cpf_cnpj')->nullable()->after('cidade');
            $table->string('susep')->nullable()->after('cpf_cnpj');
            $table->string('email1')->nullable()->after('susep');
            $table->string('email2')->nullable()->after('email1');
            $table->string('email3')->nullable()->after('email2');
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
            $table->dropColumn([
                'suc_cpd', 'estado', 'cidade', 'cpf_cnpj', 
                'susep', 'email1', 'email2', 'email3'
            ]);
        });
    }
}
