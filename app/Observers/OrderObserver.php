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
