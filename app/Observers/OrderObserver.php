<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderGroup; // Импортируем OrderGroup, если нужно получить имя/описание группы
use App\Models\FioDtrn;

use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     * Срабатывает после создания модели Order.
     */
    public function created(Order $order): void
    {
        \Log::info('OrderObserver created called', [
        'order_id' => $order->id,
        'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10)
        ]);
        // Статус "принят" имеет ID = 1 (по умолчанию из сидера)
        $this->changeStatus($order, 1); // ID статуса "принят"
        
        // Проверяем, было ли установлено поле client_invalid при создании
        if ($order->client_invalid !== null) {
            $this->syncClientInvalidFromOrder($order);
        }
        
    }

    /**
     * Handle the Order "updated" event.
     * Срабатывает после обновления модели Order.
     */
    public function updated(Order $order): void
    {
        $original = $order->getOriginal();
        
//        \Log::info('Order updated', [
//        'order_id' => $order->id,
//        'original_cancelled_at' => $original['cancelled_at'],
//        'new_cancelled_at' => $order->cancelled_at,
//        'cancelled_changed' => is_null($original['cancelled_at']) && !is_null($order->cancelled_at)
//         ]);
        
        
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
        
        // Проверяем, изменилось ли поле client_invalid
        if ($order->isDirty('client_invalid')) {
            $this->syncClientInvalidFromOrder($order);
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
        return trim($existingComment . "\n" . $newComment);
    }
    
    /**
     * Синхронизировать client_invalid у клиента и других заказов
     * на основе значения в текущем заказе $order
     */
    private function syncClientInvalidFromOrder(Order $order)
    {
        $clientId = $order->client_id;
        $newClientInvalidValue = $order->client_invalid; // Новое значение из заказа

        // Проверяем, что заказ привязан к клиенту и новое значение не null
        if ($clientId && $newClientInvalidValue !== null) {
            // 1. Обновляем поле client_invalid у клиента (fio_dtrns)
            $this->updateClientInvalid($clientId, $newClientInvalidValue, $order);

            // 2. Обновляем поле client_invalid у других незакрытых, неотмененных заказов
            $this->updateOtherOrdersClientInvalid($clientId, $newClientInvalidValue, $order);
        }
    }
    
    /**
     * Обновить поле client_invalid у клиента (fio_dtrns)
     */
    private function updateClientInvalid(int $clientId, string $newClientInvalidValue, Order $sourceOrder): void
    {
        $client = FioDtrn::find($clientId);
        if (!$client) {
            return; // Клиент не найден, выходим
        }

        $oldClientInvalidValue = $client->client_invalid;

        // Проверяем, изменилось ли значение у клиента
        if ($client->client_invalid !== $newClientInvalidValue) {
            // Формируем комментарий для клиента
            $currentOperatorName = Auth::user()?->name ?? 'Неизвестный';
            $currentDateTime = Carbon::now()->format('d.m.Y H:i');
            $clientComment = ($client->komment ? $client->komment . "\n" : '') .
                             "Замена удостоверения оп. {$currentOperatorName} через заказ №{$sourceOrder->pz_nom}: "
                             . "с '{$oldClientInvalidValue}' на '{$newClientInvalidValue}' ({$currentDateTime}).";

            // Обновляем client_invalid и komment у клиента
            $client->update([
                'client_invalid' => $newClientInvalidValue,
                'komment' => $clientComment
            ]);
        }
    }

    /**
     * Обновить поле client_invalid у других незакрытых, неотмененных заказов клиента
     */
    private function updateOtherOrdersClientInvalid(int $clientId, string $newClientInvalidValue, Order $sourceOrder): void
    {
        $currentOperatorName = Auth::user()?->name ?? 'Неизвестный';
        $currentDateTime = Carbon::now()->format('d.m.Y H:i');
        $oldClientInvalidValue = $sourceOrder->getOriginal('client_invalid'); // Получаем старое значение из БД для источника

        // Формируем комментарий в зависимости от старого значения
        if ($oldClientInvalidValue) {
            // Если старое значение было, указываем его
            $orderComment = "Удостоверение инвалида изменено оп. {$currentOperatorName} через заказ №{$sourceOrder->pz_nom}: с '{$oldClientInvalidValue}' на '{$newClientInvalidValue}' ({$currentDateTime}).";
        } else {
            // Если старого значения не было, не указываем "с ..."
            $orderComment = "Удостоверение инвалида добавлено оп. {$currentOperatorName} через заказ №{$sourceOrder->pz_nom} на '{$newClientInvalidValue}' ({$currentDateTime}).";
        }
        // --- КОНЕЦ НОВОГО ---
        // Экранируем строку комментария 
        $escapedOrderComment = addslashes($orderComment);        

        // Находим все *другие* незакрытые, неотмененные, неудаленные заказы этого клиента
        // и обновляем у них client_invalid и добавляем комментарий
        Order::where('client_id', $clientId)
            ->whereNull('taxi_sent_at') // Не переданные в такси
            ->whereNull('closed_at') // Не закрытые
            ->whereNull('cancelled_at') // Не отмененные
            ->whereNull('deleted_at') // Не удаленные
            ->where('id', '!=', $sourceOrder->id) // Исключаем текущий заказ (источник)
            ->update([
                'client_invalid' => $newClientInvalidValue,
                // Безопасное обновление комментария через DB::raw
               'komment' => \DB::raw("CONCAT_WS('\n', IFNULL(komment, ''), '" . $escapedOrderComment . "')"),
            ]);
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
