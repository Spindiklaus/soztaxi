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
        Schema::create('skidka_dops', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Наименование скидки');  
            $table->unsignedSmallInteger('skidka')->comment('Окончательная скидка инвалиду')->default(100);
            $table->unsignedSmallInteger('kol_p')->comment('Лимит поездок в месяц для инвалида')->nullable();
            $table->unsignedTinyInteger('life')->comment('Действующий')->default(1);
            $table->text('komment')->comment('Комментарии к заказу');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skidka_dops');
    }
};
