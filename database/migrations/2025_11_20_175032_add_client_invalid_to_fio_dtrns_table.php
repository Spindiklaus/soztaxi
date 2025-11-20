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
        Schema::table('fio_dtrns', function (Blueprint $table) {
            $table->string('client_invalid', 191)->after('fio')->nullable(); // Добавляем поле после 'fio', nullable
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fio_dtrns', function (Blueprint $table) {
            $table->dropColumn('client_invalid'); // Удаляем поле при откате
        });
    }
};