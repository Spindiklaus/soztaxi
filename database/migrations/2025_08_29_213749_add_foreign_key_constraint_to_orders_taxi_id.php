<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Сначала делаем поле taxi_id nullable если оно не nullable
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('taxi_id')->nullable()->change();
        });
        
        // Проверяем существующие данные
        $orphanedRecords = DB::select("
            SELECT COUNT(*) as count 
            FROM orders 
            WHERE taxi_id IS NOT NULL 
            AND taxi_id NOT IN (SELECT id FROM taxis WHERE id IS NOT NULL)
        ");
        
        if ($orphanedRecords[0]->count > 0) {
            echo "Найдено {$orphanedRecords[0]->count} записей с несуществующими taxi_id. Устанавливаю их в NULL.\n";
            DB::statement("
                UPDATE orders 
                SET taxi_id = NULL 
                WHERE taxi_id IS NOT NULL 
                AND taxi_id NOT IN (SELECT id FROM taxis WHERE id IS NOT NULL)
            ");
        }
        
        // Добавляем внешний ключ
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('taxi_id')
                  ->references('id')
                  ->on('taxis')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Удаляем внешний ключ
            try {
                $table->dropForeign(['taxi_id']);
            } catch (\Exception $e) {
                // Игнорируем ошибки
            }
        });
    }
};