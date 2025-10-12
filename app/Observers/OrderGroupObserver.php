<?php
// app/Observers/OrderGroupObserver.php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderGroup;

class OrderGroupObserver
{
     /**
     * Handle the OrderGroup "deleting" event.
     * Вызывается перед физическим удалением модели группы заказов.
     */
     public function deleting(OrderGroup $orderGroup): void
    {
        $commentToAdd = "Удален из группы ID={$orderGroup->id} (" . now() . ")."; // Создаем комментарий с ID группы и временем

        // Обновляем все связанные заказы
        $orderGroup->orders()->each(function (Order $order) use ($commentToAdd) { // $order - это экземпляр App\Models\Order
            // Используем $commentToAdd из замыкания
            $existingComment = $order->komment ?? ''; // Получаем текущий комментарий или пустую строку
            $newComment = trim($existingComment . "\n" . $commentToAdd); // Объединяем, добавляя перевод строки
            // Обновляем оба поля: order_group_id и komment
            $order->update([
                'order_group_id' => null,
                'komment' => $newComment
            ]);
        });

        // Теперь основной вызов $orderGroup->delete() в контроллере завершит удаление самой группы.
    }

    // Если вы используете Soft Deletes для OrderGroup, используйте метод forceDeleting
    // public function forceDeleting(OrderGroup $orderGroup): void
    // {
    //     $commentToAdd = "Удален из группы ID {$orderGroup->id} (" . now() . ").";
    //     $orderGroup->orders()->each(function (Order $order) use ($commentToAdd) {
    //         $existingComment = $order->komment ?? '';
    //         $newComment = trim($existingComment . "\n" . $commentToAdd);
    //         $order->update([
    //             'order_group_id' => null,
    //             'komment' => $newComment
    //         ]);
    //     });
    // }
    
    
    /**
     * Handle the OrderGroup "created" event.
     */
    public function created(OrderGroup $orderGroup): void
    {
        //
    }

    /**
     * Handle the OrderGroup "updated" event.
     */
    public function updated(OrderGroup $orderGroup): void
    {
        //
    }

    /**
     * Handle the OrderGroup "deleted" event.
     */
    public function deleted(OrderGroup $orderGroup): void
    {
        //
    }

    /**
     * Handle the OrderGroup "restored" event.
     */
    public function restored(OrderGroup $orderGroup): void
    {
        //
    }

    /**
     * Handle the OrderGroup "force deleted" event.
     */
    public function forceDeleted(OrderGroup $orderGroup): void
    {
        //
    }
}
