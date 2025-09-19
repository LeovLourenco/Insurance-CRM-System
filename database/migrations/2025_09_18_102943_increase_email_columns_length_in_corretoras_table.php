<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IncreaseEmailColumnsLengthInCorretorasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('corretoras', function (Blueprint $table) {
            $table->text('email1')->nullable()->change();
            $table->text('email2')->nullable()->change();
            $table->text('email3')->nullable()->change();
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
            $table->string('email1')->nullable()->change();
            $table->string('email2')->nullable()->change();
            $table->string('email3')->nullable()->change();
        });
    }
}
