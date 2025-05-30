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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('nmv')->comment('NMV (код категории)');
            $table->string('name')->comment('Категория');
            $table->unsignedSmallInteger('skidka')->comment('Скидка по категории')->default(50);
            $table->unsignedSmallInteger('kol_p')->comment('Лимит поездок в месяц по категории')->default(10);
            $table->unsignedbigInteger('user_id')->comment('Оператор')->index('user_id');
            $table->unsignedTinyInteger('is_soz')->comment('Работает в соцтакси')->default(1); 
            $table->unsignedTinyInteger('is_auto')->comment('Работает в легковом авто')->default(1); 
            $table->unsignedTinyInteger('is_gaz')->comment('Работает в ГАЗели')->default(1); 
            $table->text('komment')->comment('Комментарии к категории');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
