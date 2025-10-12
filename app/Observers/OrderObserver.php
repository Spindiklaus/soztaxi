<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderGroup; // Импортируем OrderGroup, если нужно получить имя/описание группы

use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\Auth;


class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        \Log::info('OrderObserver created called', [
        'order_id' => $order->id,
        'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10)
        ]);
        // Статус "принят" имеет ID = 1 (по умолчанию из сидера)
        $this->changeStatus($order, 1); // ID статуса "принят"
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        $original = $order->getOriginal();
        
        \Log::info('Order updated', [
        'order_id' => $order->id,
        'original_cancelled_at' => $original['cancelled_at'],
        'new_cancelled_at' => $order->cancelled_at,
        'cancelled_changed' => is_null($original['cancelled_at']) && !is_null($order->cancelled_at)
    ]);
        
        
        // Статус "передан в такси" - УСТАНОВКА
        if (is_null($original['taxi_sent_at']) && !is_null($order->taxi_sent_at)) {
            $this->changeStatus($order, 2); // ID статуса "передан в такси"
        }
        
         // Статус "принят" - СБРОС (когда taxi_sent_at сбрасывается в null)
        if (!is_null($original['taxi_sent_at']) && is_null($order->taxi_sent_at)) {
            \Log::info('Setting status back to accepted for order ' . $order->id);
            $this->changeStatus($order, 1); // ID статуса "принят"
        }

        // Статус "отменён"
        if (is_null($original['cancelled_at']) && !is_null($order->cancelled_at)) {
            \Log::info('Setting status to cancelled for order ' . $order->id);
            $this->changeStatus($order, 3); // ID статуса "отменён"
        }

        // Статус "закрыт"
        if (is_null($original['closed_at']) && !is_null($order->closed_at)) {
            $this->changeStatus($order, 4); // ID статуса "закрыт"
        }
        // Статус "передан в такси" - ВОССТАНОВЛЕНИЕ (когда closed_at сбрасывается, восстанавливается - передан в такси)
        if (!is_null($original['closed_at']) && is_null($order->closed_at)) {
            \Log::info('Setting status back to taxi-sent for order ' . $order->id);
            $this->changeStatus($order, 2); // ID статуса "передан в такси"
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        // Можно добавить логику при удалении, если нужно
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
    
    /**
     * Handle the Order "updating" event.
     * Вызывается перед обновлением модели Order.
     */
    public function updating(Order $order): void
    {
        // Проверяем, изменилось ли поле order_group_id
        if ($order->isDirty('order_group_id')) {
            $originalGroupId = $order->getOriginal('order_group_id'); // Старое значение
            $newGroupId = $order->order_group_id; // Новое значение

            if (is_null($originalGroupId) && !is_null($newGroupId)) {
                // Заказ добавляется в группу (был NULL, стал ID)
                $group = OrderGroup::find($newGroupId); // Получаем группу, если нужно включить её имя
                $groupName = $group ? $group->name : "ID {$newGroupId}";
                $commentToAdd = "Добавлен в группу: {$groupName} (" . now() . ").";
                $order->komment = $this->appendComment($order->getOriginal('komment'), $commentToAdd);
            } elseif (!is_null($originalGroupId) && is_null($newGroupId)) {
                // Заказ удаляется из группы (был ID, стал NULL) - это обрабатывается в OrderGroupObserver
                // Можно добавить комментарий здесь, если нужно, но логика уже в OrderGroupObserver
                // $commentToAdd = "Удален из группы ID {$originalGroupId} (" . now() . ").";
                // $order->komment = $this->appendComment($order->getOriginal('komment'), $commentToAdd);
                // В данном случае, комментарий добавляется в OrderGroupObserver, так что оставим это пустым или добавим другую логику при необходимости.
            } elseif (!is_null($originalGroupId) && !is_null($newGroupId) && $originalGroupId != $newGroupId) {
                // Заказ перемещается из одной группы в другую
                $oldGroup = OrderGroup::find($originalGroupId);
                $newGroup = OrderGroup::find($newGroupId);
                $oldGroupName = $oldGroup ? $oldGroup->name : "ID {$originalGroupId}";
                $newGroupName = $newGroup ? $newGroup->name : "ID {$newGroupId}";
                $commentToAdd = "Перемещен из группы {$oldGroupName} в группу {$newGroupName} (" . now() . ").";
                $order->komment = $this->appendComment($order->getOriginal('komment'), $commentToAdd);
            }
            // elseif (is_null($originalGroupId) && is_null($newGroupId)) { // Оба NULL - нет изменений для комментария }
        }
    }
    
    /**
     * Вспомогательный метод для объединения комментариев.
     * @param string|null $existingComment
     * @param string $newComment
     * @return string
     */
    private function appendComment(?string $existingComment, string $newComment): string
    {
        $existingComment = $existingComment ?? '';
        return trim($existingComment . "\n" . $newComment);
    }
    
    
    protected function changeStatus(Order $order, int $statusOrderId)
    {
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status_order_id' => $statusOrderId,
            'user_id' => Auth::check() ? Auth::id() : null, // Проверяем, залогинен ли пользователь
            // может быть null, если вызвано из консоли или очереди
        ]);
    }
}
