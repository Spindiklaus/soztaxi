<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('adres_otkuda_info')->nullable()->comment('Дополнительные сведения об адресе "откуда" (телефон, особенности заезда и т.д.)')->after('adres_otkuda');
            $table->text('adres_kuda_info')->nullable()->comment('Дополнительные сведения об адресе "куда" (особенности заезда и т.д.)')->after('adres_kuda');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['adres_otkuda_info', 'adres_kuda_info']);
        });
    }
};