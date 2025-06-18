<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('orders', function (Blueprint $table) {
        $table->unsignedTinyInteger('type_order')->after('id')
               ->comment('Тип заказа: 1 - соцтакси, 2 - легковое авто, 3 - ГАЗель')
               ->index(); 
    });
}

public function down()
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dropColumn('type_order');
    });
}
};
