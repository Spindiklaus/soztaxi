<?php

// app/Http/Controllers/Operator/SocialTaxiController.php
namespace App\Http\Controllers\Operator;

use App\Models\User;
use App\Models\FioDtrn; 
use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\SocialTaxiOrderService;
use App\Services\SocialTaxiOrderBuilder;
use Carbon\Carbon;

class SocialTaxiController extends BaseController
{
    protected $queryBuilder;
    protected $orderService;

    public function __construct(SocialTaxiOrderBuilder $queryBuilder, SocialTaxiOrderService $orderService)
    {
        $this->queryBuilder = $queryBuilder;
        $this->orderService = $orderService;
    }

    public function index(Request $request) {
        
        // Получаем данные для маршрутов оператора из BaseController
        $operatorRouteData = $this->getOperatorRouteData();
        $operatorRoute = $operatorRouteData['operatorRoute'];
        $operatorCurrentType = $operatorRouteData['operatorCurrentType'];
        
        // Устанавливаем фильтр по типу заказа "Соцтакси" (ID = 1) и по текущему пользователю
        if (!$request->has('filter_type_order')) {
            $request->merge(['filter_type_order' => 1]);
        }
        
        if (!$request->has('filter_user_id')) {
            $request->merge(['filter_user_id' => auth()->id()]);
        }
        // По умолчанию показываем ВСЕ записи (включая удаленные)
        $showDeletedParam = $request->get('show_deleted', '1');
        $withTrashed = match($showDeletedParam) {
            '0' => false,  // Только активные
            '1' => true,   // Все (включая удаленные)
            '2' => 'true', // Только удаленные нужно с withTrashed(true), но с дополнительным фильтром
            default => true
        };

        $sort = $request->get('sort', 'pz_data');
        $direction = $request->get('direction', 'desc');

        // Получаем список операторов для фильтра (только текущий пользователь)
        $operators = User::where('id', auth()->id())->orderBy('name')->get();

        // Собираем параметры для передачи в шаблон
        $urlParams = $this->orderService->getUrlParams();

        $query = $this->queryBuilder->build($request, $withTrashed);
        // Если нужно только удаленные, добавляем фильтр
        if ($showDeletedParam === '2') {
            $query->onlyTrashed();
        } elseif ($showDeletedParam === '0') {
            // Для "только активные" не вызываем withTrashed и onlyTrashed
            // Laravel по умолчанию исключает мягко удаленные записи
        }
        
        $orders = $query->paginate(50)->appends($request->all());
        
        
        // Сохраняем текущий тип заказа в сессии только для операторов
        session(['operator_current_type' => $request->get('type_order', 1)]);
        session(['from_operator_page' => true]);

        return view('operator-orders.index', compact(
            'orders',
            'showDeletedParam',
            'sort',
            'direction',
            'urlParams',
            'operators',
            'operatorRoute',
            'operatorCurrentType'
        ));
    }
    
   public function calendarByClient(Request $request, FioDtrn $client, $date = null) // $date определяет метод календаря
{
    // Получаем данные для маршрутов оператора из BaseController
    $operatorRouteData = $this->getOperatorRouteData();
    $operatorRoute = $operatorRouteData['operatorRoute'];
    $operatorCurrentType = $operatorRouteData['operatorCurrentType'];

    // Устанавливаем фильтр по типу заказа "Соцтакси" (ID = 1) и по выбранному клиенту
    if (!$request->has('filter_type_order')) {
        $request->merge(['filter_type_order' => 1]);
    }

    if (!$request->has('filter_client_id')) {
        $request->merge(['filter_client_id' => $client->id]);
    }

//    $showDeleted = $request->get('show_deleted', '0');
//    $sort = $request->get('sort', 'visit_data'); // Сортировка по дате поездки
//    $direction = $request->get('direction', 'asc');

    // --- ОПРЕДЕЛЕНИЕ МЕСЯЦА КАЛЕНДАРЯ ---
    $targetDate = null;
    if ($date) {
        try {
            $targetDate = \Carbon\Carbon::parse($date);
        } catch (\Exception $e) {
            // Если дата некорректна, используем текущую
            $targetDate = now();
        }
    } else {
        // Если параметр даты не передан, можно использовать текущий месяц или первый заказ
        // Пока используем текущий
        $targetDate = now();
    }

    // Определяем месяц календаря на основе $targetDate
    $calendarMonth = $targetDate->copy()->startOfMonth();
    
    // Вычисляем предыдущий и следующий месяц
    $prevMonth = $calendarMonth->copy()->subMonth();
    $nextMonth = $calendarMonth->copy()->addMonth();

    // --- ФИЛЬТРАЦИЯ ЗАКАЗОВ ЗА ВЫБРАННЫЙ МЕСЯЦ ---
    // Временно добавляем фильтр по диапазону дат для получения заказов за конкретный месяц
    
    // Удаляем filter_user_id из запроса, чтобы он не был передан в SocialTaxiOrderBuilder
    $request->merge(['filter_user_id' => null]);    
    // filter_type_order не должен влиять на календарь
    $request->merge(['filter_type_order' => null]);
    $request->merge([
        'date_from' => $calendarMonth->format('Y-m-d'),
        'date_to' => $calendarMonth->copy()->endOfMonth()->format('Y-m-d'),
    ]);
//    dd($request);

    // Получаем заказы, отфильтрованные по клиенту И по месяцу
    $query = $this->queryBuilder->build($request,  true); // с удаленными записями
    $orders = $query->with(['client', 'category', 'currentStatus', 'currentStatus.statusOrder','dopus']) // <-- Добавлен 'category'
            ->get(); // <-- Используем get(), получаем коллекцию
    $lastCategory = null;
   // Находим заказ с самой поздней датой поездки 
    $latestOrder = Order::where('client_id', $client->id)
            ->whereNull('cancelled_at') // исключаем отменённые
            ->with(['category', 'dopus'])
            ->orderBy('visit_data', 'desc')
            ->first();
     $lastCategory = $latestOrder->category; // Получаем связанную категорию
      
    // Фильтруем коллекцию, удаляя отменённые заказы ---
    $orders = $orders->filter(function ($order) {
        return $order->cancelled_at === null; // Оставляем только неотменённые
    });

    // --- ПОДГОТОВКА ДАННЫХ ДЛЯ КАЛЕНДАРЯ ---
    $calendarData = [];
    foreach ($orders as $order) {
        if ($order->visit_data) {
            $dateKey = $order->visit_data->format('Y-m-d');
            $calendarData[$dateKey][] = $order;
        }
    }

    // --- ОПРЕДЕЛЕНИЕ $startDate и $endDate ---
    // Устанавливаем startDate и endDate на начало и конец *вычисленного* месяца
    $startDate = $calendarMonth->copy()->startOfMonth();
    $endDate = $calendarMonth->copy()->endOfMonth();

    // Собираем параметры URL для передачи в шаблон и обратной навигации
    $urlParams = $this->orderService->getUrlParams();

    // Сохраняем тип заказа и путь оператора в сессию
    session(['operator_current_type' => $request->get('type_order', 1)]);
    session(['from_operator_page' => true]);

    return view('operator-orders.calendar_soz', compact(
        'client',
        'calendarData',
        'startDate',
        'endDate',
        'operatorRoute',
        'operatorCurrentType',
        'urlParams',
        'lastCategory',
        'latestOrder',    
        'prevMonth',
        'nextMonth'     
    ));
}
    
    /**
    * Копировать заказ с новой датой и направлением из календаря
    */
public function copyOrder(Request $request)
{
     // Валидация данных
    $request->validate([
        'order_id' => 'required|exists:orders,id',
        'visit_data' => 'required|date',
        'type_kuda' => 'required|in:1,2',
        'predv_way' => 'nullable|numeric|min:0',
    ]);

    try {
        $orderId = $request->input('order_id');
        $newVisitDateTime = Carbon::parse($request->input('visit_data'));
        $TypeKuda = (int) $request->input('type_kuda');
        $newPredvWay = $request->input('predv_way');

        // --- Для проверки, что дата поездки в текущем месяце ---
        $monthStart = $newVisitDateTime->copy()->startOfMonth();
        $monthEnd = $newVisitDateTime->copy()->endOfMonth();

        // Загружаем оригинальный заказ
        $originalOrder = Order::withTrashed()->find($orderId);
        
        // Подготовка данных для нового заказа
        $newOrderData = $originalOrder->toArray();
        unset($newOrderData['id']); // Удаляем ID, чтобы создать новый
        unset($newOrderData['pz_nom']); 
        unset($newOrderData['pz_data']); // pz_data генерируется автоматически
        unset($newOrderData['taxi_sent_at']);
        unset($newOrderData['order_group_id']); // убираем группировку заказа
        unset($newOrderData['taxi_price']); // факт цена поездки
        unset($newOrderData['taxi_way']); // факт километраж
        unset($newOrderData['taxi_vozm']); // сумма к возмещению
        unset($newOrderData['cancelled_at']);
        unset($newOrderData['otmena_taxi']); // убираем отменту передачи сведений в такси
        unset($newOrderData['closed_at']);
        unset($newOrderData['komment']);
        unset($newOrderData['created_at']);
        unset($newOrderData['updated_at']);
        unset($newOrderData['deleted_at']);        
       
        // ... возможно, другие поля, которые не нужно копировать, например, связанные с такси или статусами

        // Устанавливаем новые значения
        $newOrderData['user_id'] = auth()->id(); // Новый оператор (текущий)
        $newOrderData['visit_data'] = $newVisitDateTime; // Новая дата поездки
        $newOrderData['zena_type'] = 1; // У соцтакси поездки - только в одну сторону. всегда 1
        $newOrderData['predv_way'] = $newPredvWay;

        // Меняем адреса в зависимости от направления
        if ($TypeKuda == 2) { // переставляем местами откуда-куда али нет
            $newOrderData['adres_otkuda'] = $originalOrder->adres_kuda; // Было "куда" -> станет "откуда"
            $newOrderData['adres_otkuda_info'] = $originalOrder->adres_kuda_info; // Было "куда" -> станет "откуда"
            $newOrderData['adres_kuda'] = $originalOrder->adres_otkuda; // Было "откуда" -> станет "куда"
            $newOrderData['adres_kuda_info'] = $originalOrder->adres_otkuda_info; // Было "откуда" -> станет "куда"
        }
       
        $newOrderData['pz_nom'] = generateOrderNumber($originalOrder->type_order, auth()->id());
        $newOrderData['pz_data'] = now(); 
         
        // Проверка лимита поездок клиента в месяц из оригинального заказа ---
        $limit = $originalOrder->kol_p_limit; // Берём лимит из оригинального заказа
        $existingTripsCountForMonth = getClientTripsCountInMonthByVisitDate($originalOrder->client_id, $newVisitDateTime);

        // Сравниваем с лимитом. Если уже достигнут лимит, не позволяем создать ещё.
        if ($existingTripsCountForMonth >= $limit) {
            return response()->json(['success' => false, 'message' => "Невозможно создать заказ: достигнут лимит поездок для клиента ({$limit})."], 422);
        }
        
        // --- ПРОВЕРКА: Только для категорий с kat_dop = 2 и общей скидкой 100%---
        $category = $originalOrder->category;
        $message = null; 
        if ($category && $category->kat_dop == 2 &&  $originalOrder->skidka_dop_all==100) {
            // Получаем все заказы клиента в этом месяце
            $freeTripsCount = Order::where('client_id', $originalOrder->client_id)
                ->whereBetween('visit_data', [$monthStart, $monthEnd])
                ->whereNull('deleted_at')
                ->whereNull('cancelled_at')
                ->where('skidka_dop_all', '=', 100)
                ->count();

            // Если бесплатных уже >= 16, запрещаем создание
            if ($freeTripsCount >= 16) {
                $newOrderData['skidka_dop_all'] = 50; // Изменяем скидку
                $message = 'Скидка изменена с 100% на 50%, так как клиент с категорией 2 уже использовал 16 бесплатных поездок в этом месяце.';
            }
        }
        // --- КОНЕЦ ПРОВЕРКИ ---

        // Проверка, отличается ли новая дата/время от оригинальной более чем на 30 минут ---
        if ($originalOrder->visit_data) {
            $originalVisitDateTime = $originalOrder->visit_data;
            // Вычисляем абсолютную разницу в минутах
            $diffInMinutes = abs($newVisitDateTime->diffInMinutes($originalVisitDateTime));

            if ($diffInMinutes <= 60) {
                return response()->json(['success' => false, 'message' => 'Невозможно создать заказ: новая дата/время поездки должна отличаться от оригинальной более чем на 60 минут.'], 422);
            }
        }

        if (!$newVisitDateTime->between($monthStart, $monthEnd)) {
            return response()->json(['success' => false, 'message' => 'Невозможно создать заказ: дата поездки '. $newVisitDateTime.' должна быть между '.$monthStart.' и '.$monthEnd], 422);
        }
        
        
//        // Проверка ограничения: не больше 2 поездок в день
//        $existingTripsCount = Order::where('client_id', $originalOrder->client_id)
//            ->whereDate('visit_data', $newVisitDateTime->toDateString()) // Сравниваем только дату
//            ->whereNull('deleted_at') // Исключаем удаленные
//            ->whereNull('cancelled_at') // Исключаем отмененные
//            ->count();
//
//        if ($existingTripsCount >= 2) {
//            return response()->json(['success' => false, 'message' => 'Невозможно создать заказ: клиент уже имеет 2 поездки в этот день.'], 422);
//        }
 
        
        
        $directionText = ($TypeKuda == 2) ? ' (обратный путь)' : '';
        $newOrderData['komment'] = "Копия заказа {$originalOrder->pz_nom} от {$originalOrder->pz_data->format('d.m.Y H:i')}" . $directionText
                ." Выполнена из календаря поездок ". now()->format('d.m.Y H:i');
        
        // Создаем новый заказ
        $newOrder = Order::create($newOrderData);

        if ($message) {
            return response()->json(['success' => true, 'message' => $message, 'order_id' => $newOrder->id]); // Возвращаем сообщение, если оно есть
        } else {
            return response()->json(['success' => true, 'order_id' => $newOrder->id]); // Возвращаем просто success и ID, если сообщения нет
        }

    } catch (\Exception $e) {
        \Log::error('Ошибка при копировании заказа: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString()); // <-- Опционально: логгировать трейс ошибки
        return response()->json(['success' => false, 'message' => 'Произошла ошибка при создании заказа.'], 500);
    }
}

// Множественное копирование заказов ---
    /**
     * Копировать заказ несколько раз на разные даты
     */
    /**
     * Копировать заказ несколько раз на разные даты с разным временем и дальностью
     */
    public function copyMultipleOrders(Request $request)
    {
        
        $request->validate([
            'original_order_id' => 'required|exists:orders,id', // ID оригинального заказа
            'selected_dates' => 'required|array|min:1', // Массив выбранных дат
            'selected_dates.*.selected' => 'required|in:1', // Должно быть отмечено
            'selected_dates.*.visit_time' => 'required_with:selected_dates.*.selected|date_format:H:i', // Время в формате HH:MM
            'selected_dates.*.predv_way' => 'nullable|numeric|min:0', // Предв. дальность
        ]);

        $selectedDatesData = $request->input('selected_dates'); // ['2025-01-02' => ['selected' => 1, 'visit_time' => '10:30', 'predv_way' => 12.5], ...]
        $originalOrderId = $request->input('original_order_id');

        try {
            // Загружаем оригинальный заказ (включая удалённые)
            $originalOrder = Order::withTrashed()->findOrFail($originalOrderId);

            $successfulCopies = 0;
            $failedCopies = 0;
            $errorMessages = [];

            \DB::transaction(function () use ($selectedDatesData, $originalOrder, &$successfulCopies, &$failedCopies, &$errorMessages) {
                foreach ($selectedDatesData as $date => $data) {

                    // Пропускаем, если дата не отмечена (проверка по ключу 'selected')
                    if (empty($data['selected'])) {
                        continue;
                    }

                    try {
                        $newVisitTime = $data['visit_time']; // HH:MM
                        
                        \Log::info('Получена дата из запроса:', ['raw_date' => $date, 'visit_time' => $newVisitTime]);
                        
                        $newPredvWay = $data['predv_way']; // Может быть null
                        // $newDirection не передаётся, можно сделать по умолчанию 1 или добавить в календарь
                        $newDirection = 1; // По умолчанию "туда"

                        // Парсим новую дату/время
                        $newVisitDateTime = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $newVisitTime);

                        // --- Проверки идентичны copyOrder ---
                        // 1. Проверка лимита поездок в день
                        $existingTripsCount = Order::where('client_id', $originalOrder->client_id)
                            ->whereDate('visit_data', $newVisitDateTime->toDateString())
                            ->whereNull('deleted_at')
                            ->whereNull('cancelled_at')
                            ->count();

                        if ($existingTripsCount >= 2) {
                            throw new \Exception("Невозможно создать копию: клиент уже имеет 2 поездки в этот день ({$newVisitDateTime->format('d.m.Y')}).");
                        }

                        // 2. Проверка лимита поездок в месяц
                        $limit = $originalOrder->kol_p_limit;
                        $existingTripsCountForMonth = getClientTripsCountInMonthByVisitDate($originalOrder->client_id, $newVisitDateTime);

                        if ($existingTripsCountForMonth >= $limit) {
                            throw new \Exception("Невозможно создать копию: достигнут лимит поездок для клиента ({$limit}) в {$newVisitDateTime->format('m.Y')}.");
                        }

                        // 3. Проверка разницы во времени (60 минут)
                        $originalVisitDateTime = $originalOrder->visit_data;
                        $diffInMinutes = abs($newVisitDateTime->diffInMinutes($originalVisitDateTime));

                        if ($diffInMinutes <= 60) {
                            throw new \Exception("Невозможно создать копию: новая дата/время поездки ({$newVisitDateTime->format('d.m.Y H:i')}) должна отличаться от оригинальной ({$originalVisitDateTime->format('d.m.Y H:i')}) более чем на 60 минут.");
                        }

                        // --- ПРОВЕРКА: Только для категорий с kat_dop = 2 и общей скидкой 100%---
                        $category = $originalOrder->category;
                        $message = null;
                        if ($category && $category->kat_dop == 2 && $originalOrder->skidka_dop_all == 100) {
                            $monthStart = $newVisitDateTime->copy()->startOfMonth();
                            $monthEnd = $newVisitDateTime->copy()->endOfMonth();

                            $freeTripsCount = Order::where('client_id', $originalOrder->client_id)
                                ->whereBetween('visit_data', [$monthStart, $monthEnd])
                                ->whereNull('deleted_at')
                                ->whereNull('cancelled_at')
                                ->where('skidka_dop_all', '=', 100)
                                ->count();

                            if ($freeTripsCount >= 16) {
                                $newSkidkaDopAll = 50; // Изменяем скидку
                                $message = 'Скидка изменена с 100% на 50%, так как клиент с категорией 2 уже использовал 16 бесплатных поездок в этом месяце.';
                            } else {
                                $newSkidkaDopAll = $originalOrder->skidka_dop_all; // Сохраняем оригинальную скидку
                            }
                        } else {
                            $newSkidkaDopAll = $originalOrder->skidka_dop_all; // Сохраняем оригинальную скидку
                        }
                        // --- КОНЕЦ ПРОВЕРКИ ---

                        // Подготовка данных для нового заказа (как в copyOrder)
                        $newOrderData = $originalOrder->toArray();
                        unset($newOrderData['id']);
                        unset($newOrderData['pz_nom']);
                        unset($newOrderData['pz_data']);
                        unset($newOrderData['taxi_sent_at']);
                        unset($newOrderData['order_group_id']);
                        unset($newOrderData['taxi_price']);
                        unset($newOrderData['taxi_way']);
                        unset($newOrderData['taxi_vozm']);
                        unset($newOrderData['cancelled_at']);
                        unset($newOrderData['otmena_taxi']);
                        unset($newOrderData['closed_at']);
                        unset($newOrderData['komment']);
                        unset($newOrderData['created_at']);
                        unset($newOrderData['updated_at']);
                        unset($newOrderData['deleted_at']);

                        $newOrderData['user_id'] = auth()->id();
                        $newOrderData['visit_data'] = $newVisitDateTime;
                        $newOrderData['zena_type'] = 1; // Всегда 1 для соцтакси
                        $newOrderData['predv_way'] = $newPredvWay; // Устанавливаем новую предв. дальность
                        $newOrderData['skidka_dop_all'] = $newSkidkaDopAll; // Устанавливаем новую скидку

                        // Меняем адреса в зависимости от направления
                        if ($newDirection == 2) { // Обратно
                            $newOrderData['adres_otkuda'] = $originalOrder->adres_kuda;
                            $newOrderData['adres_otkuda_info'] = $originalOrder->adres_kuda_info;
                            $newOrderData['adres_kuda'] = $originalOrder->adres_otkuda;
                            $newOrderData['adres_kuda_info'] = $originalOrder->adres_otkuda_info;
                        }
                        // Адреса остаются как в оригинале, если newDirection == 1

                        $newOrderData['pz_nom'] = generateOrderNumber($originalOrder->type_order, auth()->id());
                        $newOrderData['pz_data'] = now();

                        $directionText = ($newDirection == 2) ? ' (обратный путь)' : '';
                        $newOrderData['komment'] = "Копия заказа №{$originalOrder->pz_nom} от {$originalOrder->pz_data->format('d.m.Y H:i')}" . $directionText
                                . " Выполнена из календаря поездок " . now()->format('d.m.Y H:i');
                        if ($message) {
                            $newOrderData['komment'] .= "\n" . $message;
                        }

                        // Создаем новый заказ
                        Order::create($newOrderData);
                        $successfulCopies++;

                    } catch (\Exception $e) {
                        $failedCopies++;
                        $errorMessages[] = "Дата {$date}: " . $e->getMessage();
                    }
                }
            });

            if ($failedCopies > 0) {
                $errorMessage = implode("\n", $errorMessages);
                $overallMessage = "Часть заказов не была создана:\n{$errorMessage}";
                if ($successfulCopies > 0) {
                    $overallMessage .= "\n\nУспешно создано: {$successfulCopies}";
                }
                return response()->json(['success' => false, 'message' => $overallMessage]);
            }

            return response()->json(['success' => true, 'message' => "Успешно создано {$successfulCopies} копий."]);

        } catch (\Exception $e) {
            \Log::error('Ошибка при множественном копировании заказа: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['success' => false, 'message' => 'Произошла ошибка при создании заказов.'], 500);
        }
    }

}