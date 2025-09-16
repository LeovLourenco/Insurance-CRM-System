<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsuarioIdToCorretorasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('corretoras', function (Blueprint $table) {
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            $table->index('usuario_id'); // Para performance nas queries
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
            $table->dropForeign(['usuario_id']);
            $table->dropIndex(['usuario_id']);
            $table->dropColumn('usuario_id');
        });
    }
}
