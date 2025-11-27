<?php

namespace App\Observers;

use App\Models\FioDtrn;
use App\Models\Order;

class FioDtrnObserver
{
    public function updated(FioDtrn $fioDtrn)
    {
        // Проверяем, была ли установлена дата смерти (ранее была NULL)
        if ($fioDtrn->isDirty('rip_at') && $fioDtrn->rip_at) {
            $this->cancelOrdersOnRip($fioDtrn);
        }
    }

    protected function cancelOrdersOnRip(FioDtrn $fioDtrn)
    {
        // Получаем заказы клиента, которые не переданы в такси и не отменены
        $ordersToCancel = $fioDtrn->orders()
            ->whereNull('taxi_sent_at')  // Не переданы в такси
            ->whereNull('cancelled_at')   // Не отменены
            ->get();

        foreach ($ordersToCancel as $order) {
            // Устанавливаем дату отмены
            $order->cancelled_at = $fioDtrn->rip_at;
            
            // Добавляем комментарий об отмене
            $cancelComment = 'Отменен автоматически: клиент умер ' . $fioDtrn->rip_at->format('d.m.Y');
            if ($order->komment) {
                $order->komment .= "\n" . $cancelComment;
            } else {
                $order->komment = $cancelComment;
            }
            
            $order->save();
        }
    }
}