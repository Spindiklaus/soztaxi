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
        Schema::create('fio_rips', function (Blueprint $table) {
            $table->id();
            $table->string('kl_id')->comment('Серия^Паспорт')->nullable();
            $table->string('fio')->comment('ФИО');
            $table->date('data_r')->comment('Дата рождения')->nullable();
            $table->char('sex',1)->comment('Пол')->index('sex');
            $table->string('adres')->comment('Адрес')->nullable();
            $table->dateTime('rip_at')->comment('Дата смерти')->index('rip_at');
            $table->string('nom_zap')->comment('Номер записи в ЗАГС')->nullable();
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
        Schema::dropIfExists('fio_rips');
    }
};
