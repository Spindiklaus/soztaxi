<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueIndexesToFioRipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fio_rips', function (Blueprint $table) {
            // Добавляем уникальный индекс на поле kl_id
            $table->unique('kl_id');
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
            // Удаляем уникальный индекс на поле kl_id
            $table->dropUnique(['kl_id']);
        });
    }
}