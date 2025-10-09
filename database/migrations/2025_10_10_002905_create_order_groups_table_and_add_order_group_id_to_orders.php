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
        // Создаем таблицу order_groups
        Schema::create('order_groups', function (Blueprint $table) {
            $table->id(); // bigint UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->string('name')->nullable(); // Опциональное имя/описание группы
            $table->timestamps(); // created_at, updated_at
        });

        // Добавляем поле order_group_id в таблицу orders
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('order_group_id')->nullable()->after('taxi_id'); // Добавляем после taxi_id
            // Создаем индекс для улучшения производительности JOIN и WHERE
            $table->index('order_group_id');
            // Добавляем внешний ключ (опционально, но рекомендуется для целостности данных)
            // Убедитесь, что в таблице order_groups есть id перед созданием внешнего ключа
            $table->foreign('order_group_id')->references('id')->on('order_groups')->onDelete('set null'); // Устанавливаем NULL при удалении группы
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Удаляем внешний ключ и индекс перед удалением столбца
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['order_group_id']); // Удаляем внешний ключ
            $table->dropIndex(['order_group_id']); // Удаляем индекс
        });

        // Удаляем столбец order_group_id
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('order_group_id');
        });

        // Удаляем таблицу order_groups
        Schema::dropIfExists('order_groups');
    }
};