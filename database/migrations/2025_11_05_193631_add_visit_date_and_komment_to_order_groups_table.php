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
            $table->dateTime('visit_date')->after('id'); 
            $table->text('komment')->after('name'); 
        });
        
        // Добавляем индекс для поля visit_date для улучшения производительности сортировки
        Schema::table('order_groups', function (Blueprint $table) {
            $table->index('visit_date', 'idx_order_groups_visit_date');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_groups', function (Blueprint $table) {
            $table->dropIndex(['idx_order_groups_visit_date']); // Удаляем индекс
            $table->dropColumn(['visit_date', 'komment']);
        });
        
    }
};