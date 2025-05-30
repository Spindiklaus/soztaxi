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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedbigInteger('client_id')->comment('Инвалид')->index('client_id');
            $table->string('client_tel')->comment('Телефон контакта');
            $table->string('client_invalid')->comment('Удостоверение инвалида')->nullable();
            $table->string('client_sopr')->comment('Сопровождающий')->nullable();            
            $table->unsignedbigInteger('category_id')->comment('Категория инвалидности')->index('category_id');
            $table->unsignedSmallInteger('category_skidka')->comment('Скидка по категории инвалида');
            $table->unsignedSmallInteger('category_limit')->comment('Лимит поездок по категории в месяц');
            $table->unsignedbigInteger('dopus_id')->comment('Дополнительные условия для скидок (гемодиализ)')->index('dopus_id')->nullable(); 
            $table->unsignedSmallInteger('skidka_dop_all')->comment('Окончательная скидка инвалиду')->nullable(); 
            $table->unsignedSmallInteger('kol_p_limit')->comment('Окончательный лимит поездок в месяц для инвалида')->nullable();            
            
            $table->string('pz_nom')->comment('Номер заказа');
            $table->dateTime('pz_data')->comment('Дата заказа')->index('pz_data');
            $table->string('adres_otkuda')->comment('Откуда ехать');
            $table->string('adres_kuda')->comment('Куда ехать');
            $table->string('adres_obratno')->comment('Обратный адрес, если есть')->nullable();
            $table->unsignedSmallInteger('zena_type')->comment('Тип стоимости (1 - только туда, 2 - туда и обратно')->default(1);
            
            $table->dateTime('visit_data')->comment('Дата поездки')->index('visit_data');
            $table->decimal('predv_way', 9,3)->comment('Предварительная дальность поездки')->nullable();            
            $table->unsignedbigInteger('taxi_id')->comment('Оператор такси')->index('taxi_id');
            
            $table->dateTime('taxi_sent_at')->comment('Дата передачи сведений диспетчеру такси')->index('taxi_sent_at')->nullable();
            $table->decimal('taxi_price',18,11)->comment('Фактическая цена поездки (из такси)')->nullable();
            $table->decimal('taxi_way',9,3)->comment('Километраж фактический')->nullable();
            
            $table->dateTime('cancelled_at')->comment('Дата отмены заказа')->index('cancelled_at')->nullable(); 
            $table->unsignedTinyInteger('otmena_taxi')->comment('Подтверждение отмены заказа у оператора такси в случае необходимости, (1 - сообщили)')->default(0); 
            $table->dateTime('closed_at')->comment('Дата закрытия заказа')->index('closed_at')->nullable();                        

            $table->text('komment')->comment('КОмментарии к заказу');
            $table->unsignedbigInteger('user_id')->comment('Оператор заказа')->index('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
