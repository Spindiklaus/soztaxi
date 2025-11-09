<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_groups', function (Blueprint $table) {
            $table->decimal('taxi_way', 9, 3)->nullable()->after('name')->comment('Километраж фактический');
            $table->decimal('taxi_price', 18, 11)->nullable()->after('taxi_way')->comment('Фактическая цена поездки');
            $table->decimal('taxi_vozm', 18, 11)->nullable()->after('taxi_price')->comment('Сумма к возмещению');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_groups', function (Blueprint $table) {
           $table->dropColumn(['taxi_way', 'taxi_price', 'taxi_vozm']);
        });
        
    }
};