<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueIndexToNomZapInFioRipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fio_rips', function (Blueprint $table) {
            // Добавляем уникальный индекс на поле nom_zap
            $table->unique('nom_zap');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fio_rips', function (Blueprint $table) {
            // Удаляем уникальный индекс
            $table->dropUnique(['nom_zap']);
        });
    }
}