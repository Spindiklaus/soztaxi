<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\OrderGroup;
use App\Services\OrderGroupingService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;



class OrderGroupingController extends BaseController {

    protected $groupingService;

    public function __construct(OrderGroupingService $groupingService) {
        $this->groupingService = $groupingService;
    }

    // Показать форму выбора даты
    public function showGroupingForm() {

        // Определяем дату начала периода (2 месяца назад от сегодня)
        $twoMonthsAgo = \Carbon\Carbon::now()->subMonths(6)->startOfDay();

        // Получаем даты и количество несгруппированных, незакрытых, неотмененных заказов типа 1
        // и только со статусом "Переданные в такси, поскольку "принятые" могут еще редактироваться
        $groupingDates = Order::selectRaw('DATE(visit_data) as grouping_date, COUNT(*) as count')
                ->where('visit_data', '>=', $twoMonthsAgo) // Ограничение на дату                
                ->whereNull('closed_at')
                ->whereNull('cancelled_at')
                ->whereNotNull('taxi_sent_at')
                ->where ('taxi_way', '>', 0)
                ->whereNull('order_group_id')
                ->where('type_order', 1)
                ->groupBy('grouping_date')
                ->orderBy('grouping_date', 'desc')
                ->pluck('count', 'grouping_date'); // Возвращает коллекцию, где ключ - дата, значение - количество


        return view('orders-grouping.grouping_form', compact('groupingDates')); // Blade шаблон для выбора даты
    }

    // Обработать выбор даты и показать заказы для группировки
    public function showOrdersForGrouping(Request $request) {
        $request->validate([
            'grouping_date' => 'required|date',
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
                ->whereNotNull('taxi_sent_at')
                ->where ('taxi_way', '>', 0)
                ->whereNull('order_group_id')  // не сгруппированные 
                ->with([
                    'client', // Загружаем связь client (метод в модели Order)   
                    'currentStatus', // Загружаем текущий статус
                    'currentStatus.statusOrder' // Загружаем связь statusOrder для текущего статуса
                ])   
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
        
        // Определяем кастомные сообщения
    $messages = [
        'selected_groups.required' => 'Необходимо выбрать хотя бы одну группу.',
        'selected_groups.array' => 'Выбранные группы должны быть представлены в виде массива.',
        'selected_groups.*.order_ids.required' => 'В каждой выбранной группе должен быть указан список заказов.',
        'selected_groups.*.order_ids.array' => 'Заказы в группе должны быть представлены в виде массива.',
        'selected_groups.*.order_ids.max' => 'В одной группе не может быть более :max заказов.',
        'selected_groups.*.order_ids.*.required' => 'ID заказа в группе не может быть пустым.',
        'selected_groups.*.order_ids.*.exists' => 'Один или несколько выбранных заказов не существуют в базе данных.',
    ];
        
        try {
            Validator::make($request->all(), [
                'selected_groups' => 'required|array',
                'selected_groups.*.order_ids' => 'required|array|max:3',
                'selected_groups.*.order_ids.*' => 'required|exists:orders,id',
            ], $messages)->validate();
        } catch (ValidationException $e) {
            // Если валидация не проходит, редиректим на форму выбора даты
            return redirect()->route('orders.grouping.form')->withErrors($e->errors())->withInput();
        }

        // Если валидация пройдена, продолжаем
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

                    // Берем дату поездки из первого заказа 
                    $visitDate = $earliestOrder->visit_data;

                    // Получаем имя текущего оператора 
                    $currentOperatorName = auth()->user()->name ?? 'Неизвестный'; // 
                    // Формируем комментарий
                    $comment = "Сформирована оператором {$currentOperatorName} по методу адреса доставки";
                }
                else {
                    // На всякий случай, если заказы не найдены (хотя валидация должна это исключить)
                    $groupName = 'Группа (ошибка)';
                    $visitDate = now(); // Резервная дата/время
                    $comment = 'Ошибка при формировании группы'; // Резервный комментарий
                }
                // --- КОНЕЦ ИЗМЕНЕНИЯ ---
                // Создаем новую группу в БД с сформированным именем
                $orderGroup = OrderGroup::create([
                            'name' => $groupName,
                            'visit_date' => $visitDate, // Добавляем дату поездки
                            'komment' => $comment      // Добавляем комментарий
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
