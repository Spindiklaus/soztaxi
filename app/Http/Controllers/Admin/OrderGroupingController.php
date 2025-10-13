<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\OrderGroup;
use App\Services\OrderGroupingService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OrderGroupingController extends BaseController {

    protected $groupingService;

    public function __construct(OrderGroupingService $groupingService) {
        $this->groupingService = $groupingService;
    }

    // Показать форму выбора даты
    public function showGroupingForm() {
        return view('orders-grouping.grouping_form'); // Blade шаблон для выбора даты
    }

    // Обработать выбор даты и показать заказы для группировки
    public function showOrdersForGrouping(Request $request) {
        $request->validate([
            'grouping_date' => 'required|date',
            // Валидация новых параметров
            'time_tolerance' => 'required|integer|min:20|max:60', // Пример: от 20 до 60 минут
            'address_tolerance' => 'required|numeric|min:20|max:100', // Пример: от 20 до 100%
            'max_potential_group_size' => 'required|integer|min:1|max:20', // Установите логичный максимум            
        ]);

        $selectedDate = Carbon::parse($request->input('grouping_date'))->startOfDay();
        $endDate = $selectedDate->copy()->endOfDay();
        // Получаем параметры из запроса
        $timeTolerance = (int) $request->input('time_tolerance');
        $addressTolerance = (float) $request->input('address_tolerance');
        $maxPotentialGroupSize = (int) $request->input('max_potential_group_size'); // Получаем новое значение


        // Получаем заказы типа 1 (соцтакси) на выбранную дату, не закрытые и не отмененные
        $orders = Order::where('type_order', 1)
                ->whereBetween('visit_data', [$selectedDate, $endDate])
                ->whereNull('closed_at')
                ->whereNull('cancelled_at')
                ->whereNull('order_group_id')  // не сгруппированные 
                ->with(['client']) // Загружаем связь client (метод в модели Order)   
                ->orderBy('visit_data')
                ->orderBy('adres_otkuda')
                ->orderBy('adres_kuda')
                ->get();

//        \Log::info('Orders for grouping:', $orders->pluck('id', 'pz_nom')->toArray()); // Логируем номера и ID
//        $orderIds = $orders->pluck('id')->toArray();
//        $uniqueOrderIds = array_unique($orderIds);
//        if (count($orderIds) !== count($uniqueOrderIds)) {
//            \Log::warning('Duplicate Order IDs found in initial collection!', [
//                'original_count' => count($orderIds),
//                'unique_count' => count($uniqueOrderIds),
//                'duplicates' => array_diff_assoc($orderIds, $uniqueOrderIds)
//            ]);
//        }
        // Получаем толерантность времени из сервиса
        // $timeTolerance = $this->groupingService->getTimeToleranceMinutes(); // 
        // Генерируем потенциальные группы
        $potentialGroups = $this->groupingService->findPotentialGroupsForDate($orders, $timeTolerance, $addressTolerance, $maxPotentialGroupSize);

        return view('orders-grouping.grouping_view', compact('orders', 'potentialGroups', 'selectedDate', 'timeTolerance', 'addressTolerance', 'maxPotentialGroupSize'));
    }

    // Обработать выбор группировки пользователем и сохранить
    public function processGrouping(Request $request) {
        $request->validate([
            'selected_groups' => 'required|array',
            'selected_groups.*.order_ids' => 'required|array|max:3',
            'selected_groups.*.order_ids.*' => 'required|exists:orders,id',
        ]);

        $selectedGroups = $request->input('selected_groups');

        \DB::transaction(function () use ($selectedGroups) {
            foreach ($selectedGroups as $groupData) {
                if (empty($groupData['order_ids'])) {
                    continue; // Пропускаем пустые группы
                }
                $groupIds = $groupData['order_ids'];
                // --- Получаем модели и формируем имя ---
                // Получаем модели Order
                $ordersToUpdate = Order::whereIn('id', $groupIds)->get();

                if ($ordersToUpdate->isNotEmpty()) {
                    // Находим заказ с самым ранним visit_data (первый в группе)
                    $earliestOrder = $ordersToUpdate->sortBy('visit_data')->first();
                    $earliestVisitTime = $earliestOrder->visit_data;

                    // Находим заказ с самым поздним visit_data (последний в группе)
                    $latestOrder = $ordersToUpdate->sortByDesc('visit_data')->first();
                    $latestVisitTime = $latestOrder->visit_data;

                    // Берем адрес "куда" из первого заказа
                    $destinationAddress = $earliestOrder->adres_kuda;

                    // Количество заказов в группе (человек)
                    $countOrders = $ordersToUpdate->count();

                    // Формируем имя группы по новому шаблону
                    $groupName = "До: {$destinationAddress} | Время {$earliestVisitTime->format('H:i')} - {$latestVisitTime->format('H:i')} ({$countOrders} чел.)";
                }
                else {
                    // На всякий случай, если заказы не найдены (хотя валидация должна это исключить)
                    $groupName = 'Группа (ошибка)';
                }
                // --- КОНЕЦ ИЗМЕНЕНИЯ ---
                // Создаем новую группу в БД с сформированным именем
                $orderGroup = OrderGroup::create([
                            'name' => $groupName,
                ]);

                foreach ($ordersToUpdate as $order) {
                    // Вызываем update() на экземпляре модели Order
                    // Это запустит событие 'updating' и, соответственно, OrderObserver::updating()
                    $order->update(['order_group_id' => $orderGroup->id]);
                }
            }
        });

        // Редирект на форму выбора даты
        return redirect()->route('orders.grouping.form')->with('success', 'Группировка успешно сохранена!');
    }

}
