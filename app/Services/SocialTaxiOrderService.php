<?php

namespace App\Services;

use App\Models\Order;
use App\Models\FioDtrn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Category;
use App\Models\Taxi;
use App\Models\SkidkaDop;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class SocialTaxiOrderService {

    /**
     * Создание заказа по типу
     */
    public function createOrderByType(array $validatedData, int $type) {
        DB::beginTransaction();
        try {
            // Используем номер заказа из формы
            $pzNom = $validatedData['pz_nom'];

            // Проверяем, существует ли уже заказ с таким номером
            if (Order::where('pz_nom', $pzNom)->exists()) {
                throw new \Exception("Заказ с номером {$pzNom} уже существует.");
            }

            // Подготавливаем данные для создания заказа
            $orderData = $this->prepareOrderData($validatedData);

            // Создаем заказ
            $order = Order::create($orderData);

            // Устанавливаем начальный статус "Принят"
            // не надо, а то устанавливает 2 раза статус в order_history_status
            // $this->setInitialStatus($order, 1); // ID статуса "Принят"

            DB::commit();

            return $order;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Ошибка при создании заказа", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Обновление заказа
     */
    public function updateOrder(Order $order, array $validatedData) {
        DB::beginTransaction();
        try {
            // Подготавливаем данные для обновления заказа
            $orderData = $this->prepareOrderData($validatedData);

            // Обновляем заказ
            $order->update($orderData);

            DB::commit();

            return $order;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Ошибка при обновлении заказа", [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Получение данных клиента
     */
    public function getClientData(int $clientId, int $typeOrder = 1) {
        try {
            // Получаем клиента
            $client = FioDtrn::find($clientId);
            if (!$client) {
                throw new \Exception('Клиент не найден');
            }

            // Получаем последние данные из предыдущих заказов клиента
            $lastOrder = Order::where('client_id', $clientId)
                    ->where('type_order', $typeOrder)
                    ->whereNotNull('visit_data')
                    ->whereNull('deleted_at')
                    ->whereNull('cancelled_at')
                    ->orderBy('visit_data', 'desc')
                    ->first();

            // Получаем категории клиента из предыдущих заказов
            $clientCategories = Order::where('client_id', $clientId)
                    ->where('type_order', $typeOrder)
                    ->whereNotNull('category_id')
                    ->whereNull('deleted_at')
                    ->whereNull('cancelled_at')
                    ->distinct()
                    ->pluck('category_id')
                    ->toArray();

            return [
                'client' => [
                    'id' => $client->id,
                    'fio' => $client->fio,
                    'kl_id' => $client->kl_id,
                    'rip_at' => $client->rip_at,
                ],
                'last_order_data' => $lastOrder ? [
            'client_tel' => $lastOrder->client_tel,
            'client_invalid' => $lastOrder->client_invalid,
            'client_sopr' => $lastOrder->client_sopr,
            'category_id' => $lastOrder->category_id,
            'category_skidka' => $lastOrder->category_skidka,
            'category_limit' => $lastOrder->category_limit,
            'dopus_id' => $lastOrder->dopus_id,
            'skidka_dop_all' => $lastOrder->skidka_dop_all,
            'kol_p_limit' => $lastOrder->kol_p_limit,
                ] : null,
                'client_categories' => $clientCategories,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Ошибка получения данных клиента: ' . $e->getMessage());
        }
    }

    /**
     * Подготовка данных заказа
     */
    private function prepareOrderData(array $validatedData): array {
        return [
            'type_order' => (int) ($validatedData['type_order'] ?? 1),
            'client_id' => (int) ($validatedData['client_id'] ?? 0),
            'client_tel' => $validatedData['client_tel'] ?? null,
            'client_invalid' => $validatedData['client_invalid'] ?? null,
            'client_sopr' => $validatedData['client_sopr'] ?? null,
            'category_id' => (int) ($validatedData['category_id'] ?? 0),
            'category_skidka' => $validatedData['category_skidka'] ? (int) $validatedData['category_skidka'] : null,
            'category_limit' => $validatedData['category_limit'] ? (int) $validatedData['category_limit'] : null,
            'dopus_id' => !empty($validatedData['dopus_id']) ? (int) $validatedData['dopus_id'] : null,
            'skidka_dop_all' => $validatedData['skidka_dop_all'] ? (int) $validatedData['skidka_dop_all'] : null,
            'kol_p_limit' => $validatedData['kol_p_limit'] ? (int) $validatedData['kol_p_limit'] : null,
            'pz_nom' => $validatedData['pz_nom'],
            'pz_data' => $validatedData['pz_data'] ?? now(),
            'adres_otkuda' => $validatedData['adres_otkuda'] ?? null,
            'adres_kuda' => $validatedData['adres_kuda'] ?? null,
            'adres_obratno' => $validatedData['adres_obratno'] ?? null,
            'zena_type' => (int) ($validatedData['zena_type'] ?? 1),
            'visit_data' => $validatedData['visit_data'] ?? null,
            'visit_obratno' => $validatedData['visit_obratno'] ?? null,
            'predv_way' => isset($validatedData['predv_way']) && $validatedData['predv_way'] !== '' && $validatedData['predv_way'] !== null ?
            (float) str_replace(',', '.', $validatedData['predv_way']) : null,
            'taxi_id' => !empty($validatedData['taxi_id']) ? (int) $validatedData['taxi_id'] : null,
            'taxi_sent_at' => $validatedData['taxi_sent_at'] ?? null,
            'taxi_price' => isset($validatedData['taxi_price']) && $validatedData['taxi_price'] !== '' && $validatedData['taxi_price'] !== null ?
            (float) str_replace(',', '.', $validatedData['taxi_price']) : null,
            'taxi_vozm' => isset($validatedData['taxi_vozm']) && $validatedData['taxi_vozm'] !== '' && $validatedData['taxi_vozm'] !== null ?
            (float) str_replace(',', '.', $validatedData['taxi_vozm']) : null,
            'taxi_way' => isset($validatedData['taxi_way']) && $validatedData['taxi_way'] !== '' && $validatedData['taxi_way'] !== null ?
            (float) str_replace(',', '.', $validatedData['taxi_way']) : null,
            'cancelled_at' => $validatedData['otmena_data'] ?? null,
            'otmena_taxi' => (int) ($validatedData['otmena_taxi'] ?? 0),
            'closed_at' => $validatedData['closed_at'] ?? null,
            'komment' => $validatedData['komment'] ?? null,
            'user_id' => (int) ($validatedData['user_id'] ?? auth()->id() ?? 1),
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
            'visit_obratno' => $validatedData['visit_obratno'] ?? null,
        ];
    }

    /**
     * Установка начального статуса заказа
     */
    private function setInitialStatus(Order $order, int $statusId) {
        DB::table('order_status_histories')->insert([
            'order_id' => $order->id,
            'status_order_id' => $statusId,
            'user_id' => auth()->id() ?? 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Возвращает детали заказа для отображения.
     *
     * @param int $id
     * @return array
     * @throws ModelNotFoundException
     */
    public function getOrderDetails(int $id): array {
        // Поиск заказа с удаленными записями
        $order = Order::withTrashed()->find($id);

        if (!$order) {
            // Бросаем исключение, которое может быть обработано в контроллере или глобально
            throw new ModelNotFoundException('Заказ не найден.');
        }

        // Загружаем все необходимые отношения
        $order->load([
            'client',
            'category',
            'dopus',
            'statusHistory.statusOrder',
            'statusHistory.user',
            'user',
            'taxi'
        ]);
        $tripCount = getClientTripsCountInMonthByVisitDate($order->client_id, $order->visit_data);

        // Возвращаем массив данных, готовый для передачи в представление
        return [
            'order' => $order,
            'tripCount' => $tripCount,
            'taxi' => $order->taxi // Такси уже загружено через load()
        ];
    }

    /**
     * редактирование заказа
     */
    public function getOrderEditData(int $id): array {
        $order = Order::find($id);

        if (!$order) {
            throw new ModelNotFoundException('Заказ не найден.');
        }

        // Загружаем только необходимые отношения
        $order->load(['client', 'category', 'dopus']);

        // Для редактирования получаем только клиента и категорию из текущего заказа
        $client = FioDtrn::find($order->client_id);
        $category = Category::find($order->category_id);
        // Получаем списки для выпадающих меню (только активные записи)
        $taxis = Taxi::where('life', 1)->orderBy('name')->get();
        $dopus = SkidkaDop::find($order->dopus_id); // Получаем текущие дополнительные условия

        return [
            'order' => $order,
            'client' => $client,
            'category' => $category,
            'taxis' => $taxis,
            'dopus' => $dopus,
        ];
    }

    /**
     * Подготавливает данные для формы создания заказа.
     * @param int $type
     * @return array
     */
    public function getOrderCreateData(int $type): array {

        // Получаем список категорий
        $categories = Category::where(function ($query) use ($type) {
                    switch ($type) {
                        case 1:
                            $query->where('is_soz', 1);
                            break;
                        case 2:
                            $query->where('is_auto', 1);
                            break;
                        case 3:
                            $query->where('is_gaz', 1);
                            break;
                    }
                })->orderBy('nmv')->get();

        // Получаем список операторов такси
        $taxis = Taxi::where('life', 1)->orderBy('name')->get();
        $defaultTaxiId = null;
        if ($taxis->count() == 1) {
            $defaultTaxiId = $taxis->first()->id;
        }

        // Получаем ID разрешенных категорий
        $allowedCategoryIds = $categories->pluck('id')->toArray();

        // Получаем список клиентов, которые имели заказы в разрешенных категориях
        $clients = FioDtrn::whereNull('rip_at')
                ->whereHas('orders', function ($query) use ($allowedCategoryIds) {
                    $query->whereIn('category_id', $allowedCategoryIds)
                    ->whereNull('deleted_at')
                    ->whereNull('cancelled_at');
                })
                ->orderBy('fio')
                ->get();

        // Генерация номера заказа и времени
        $orderNumber = generateOrderNumber($type, auth()->id());
        $orderDateTime = now();

        // Получаем список дополнительных условий
        $dopusConditions = SkidkaDop::where('life', 1)->orderBy('name')->get();

        return compact(
                'type',
                'clients',
                'categories',
                'taxis',
                'defaultTaxiId',
                'orderNumber',
                'orderDateTime',
                'dopusConditions'
        );
    }

    /**
     * Получение данных заказа для копирования
     */
    public function getOrderDataForCopy(int $orderId, int $type): array {
        try {
            $order = Order::find($orderId);

            if (!$order) {
                return [];
            }

            // Проверяем, что тип совпадает
            if ($order->type_order != $type) {
                return [];
            }

            return [
                'copiedOrder' => $order,
            ];
        } catch (\Exception $e) {
            \Log::error("Ошибка получения данных для копирования заказа", [
                'order_id' => $orderId,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function cancelOrder(Order $order, array $validatedData) {
        // Проверяем, что заказ не удален
        if ($order->deleted_at) {
            throw new \Exception('Невозможно отменить удаленный заказ.');
        }

        // Проверяем, что заказ еще не отменен
        if ($order->cancelled_at) {
            throw new \Exception('Заказ уже отменен.');
        }

        // Проверяем, что у заказа есть статус "Принят" (ID = 1)
        $currentStatus = $order->currentStatus;
        $statusId = $currentStatus ? $currentStatus->status_order_id : 1;

        if ($statusId != 1) {
            throw new \Exception('Отмена возможна только для заказов со статусом "Принят".');
        }

        \DB::beginTransaction();
        try {
            // Устанавливаем cancelled_at напрямую - это должно сработать в Observer
            $order->cancelled_at = $validatedData['cancelled_at'];
            $order->komment = ($order->komment ? $order->komment . "\n\n" : '') .
                    'Отмена заказа: ' . $validatedData['reason'] .
                    '. Оператор: ' . auth()->user()->name .
                    ' (' . auth()->user()->litera . ')' .
                    '. Дата отмены: ' . Carbon::parse($validatedData['cancelled_at'])->format('d.m.Y H:i');
            $order->updated_at = now();
            $order->save();

            \DB::commit();

            return $order;
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    public function getUrlParams() {
        $params = request()->only([
                    'sort', 'direction', 'show_deleted', 'filter_pz_nom',
                    'filter_type_order', 'status_order_id', 'date_from', 'date_to', 'filter_user_id', 
                    'client_fio', 'page'
        ]);
        // \Log::info('GetUrlParams result', ['params' => $params]);
        return $params;
    }

}
