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
        Schema::create('taxis', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Оператор такси');
            $table->unsignedTinyInteger('life')->comment('Действующий')->default(1); 
            $table->unsignedbigInteger('user_id')->comment('Оператор')->index('user_id');
            $table->decimal('koef', 18,11)->comment('Стоимость 1 км пути');            
            $table->decimal('posadka', 18,11)->comment('Стоимость посадки'); 
            $table->decimal('koef50', 18,11)->comment('Стоимость 1 км пути при 50% скидке');            
            $table->decimal('posadka50', 18,11)->comment('Стоимость посадки при 50% скидке');              
            $table->decimal('zena1_auto', 18,11)->comment('Цена легкового авто в одну сторону');            
            $table->decimal('zena2_auto', 18,11)->comment('Цена легкового авто в обе стороны');              
            $table->decimal('zena1_gaz', 18,11)->comment('Цена ГАЗели в одну сторону');            
            $table->decimal('zena2_gaz', 18,11)->comment('Цена ГАЗели в обе стороны');
            $table->text('komment')->comment('Комментарии')->nullable;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxis');
    }
};
