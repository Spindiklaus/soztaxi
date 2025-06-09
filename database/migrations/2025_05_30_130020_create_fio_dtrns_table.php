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
        Schema::create('fio_dtrns', function (Blueprint $table) {
            $table->id();
            $table->string('kl_id')->comment('Серия^Паспорт')->unique();
            $table->string('fio')->comment('ФИО');
            $table->date('data_r')->comment('Дата рождения')->nullable();
            $table->char('sex',1)->comment('Пол')->nullable();
            $table->dateTime('rip_at')->comment('Дата смерти')->index('rip_at')->nullable();
            $table->dateTime('created_rip')->comment('Дата внесения информации о RIP')->nullable();
            $table->unsignedbigInteger('user_rip')->comment('Оператор занесения информации о RIP')->index('user_rip')->nullable();            
            $table->unsignedbigInteger('user_id')->comment('Оператор занесения ФИО')->index('user_id');
            $table->text('komment')->comment('Комментарии')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fio_dtrns');
    }
};
