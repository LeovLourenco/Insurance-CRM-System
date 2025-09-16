<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropComercialIdFromCorretorasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('corretoras', function (Blueprint $table) {
            $table->dropForeign(['comercial_id']);
            $table->dropIndex(['comercial_id']);
            $table->dropColumn('comercial_id');
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
            $table->foreignId('comercial_id')->nullable()->constrained('users')->onDelete('set null');
            $table->index('comercial_id');
        });
    }
}
