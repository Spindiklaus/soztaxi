<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\Auth;


class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        // Статус "принят" имеет ID = 1 (по умолчанию из сидера)
        $this->changeStatus($order, 1); // ID статуса "принят"
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        $original = $order->getOriginal();
        // Статус "передан в такси"
        if (is_null($original['taxi_sent_at']) && !is_null($order->taxi_sent_at)) {
            $this->changeStatus($order, 2); // ID статуса "передан в такси"
        }

        // Статус "отменён"
        if (is_null($original['cancelled_at']) && !is_null($order->cancelled_at)) {
            $this->changeStatus($order, 3); // ID статуса "отменён"
        }

        // Статус "закрыт"
        if (is_null($original['closed_at']) && !is_null($order->closed_at)) {
            $this->changeStatus($order, 4); // ID статуса "закрыт"
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
