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

        $showDeleted = $request->get('show_deleted', '0');
        $sort = $request->get('sort', 'pz_data');
        $direction = $request->get('direction', 'desc');

        // Получаем список операторов для фильтра (только текущий пользователь)
        $operators = User::where('id', auth()->id())->orderBy('name')->get();

        // Собираем параметры для передачи в шаблон
        $urlParams = $this->orderService->getUrlParams();

        $query = $this->queryBuilder->build($request, $showDeleted == '1');
        
        $orders = $query->paginate(100)->appends($request->all());
        
        
        // Сохраняем текущий тип заказа в сессии только для операторов
        session(['operator_current_type' => $request->get('type_order', 1)]);
        session(['from_operator_page' => true]);

        return view('operator-orders.index', compact(
            'orders',
            'showDeleted',
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

    $showDeleted = $request->get('show_deleted', '0');
    $sort = $request->get('sort', 'visit_data'); // Сортировка по дате поездки
    $direction = $request->get('direction', 'asc');

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
    // --- КОНЕЦ ОПРЕДЕЛЕНИЯ МЕСЯЦА ---

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
    // --- КОНЕЦ ФИЛЬТРАЦИИ ---

    // Получаем заказы, отфильтрованные по клиенту И по месяцу
    $query = $this->queryBuilder->build($request, $showDeleted == '1');
    $orders = $query->get(); // <-- Используем get(), получаем коллекцию
    
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
    // --- КОНЕЦ ПОДГОТОВКИ ---

    // --- ОПРЕДЕЛЕНИЕ $startDate и $endDate ---
    // Устанавливаем startDate и endDate на начало и конец *вычисленного* месяца
    $startDate = $calendarMonth->copy()->startOfMonth();
    $endDate = $calendarMonth->copy()->endOfMonth();
    // --- КОНЕЦ ОПРЕДЕЛЕНИЯ ---

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
        'urlParams'
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
    ]);

    try {
        $orderId = $request->input('order_id');
        $newVisitDateTime = Carbon::parse($request->input('visit_data'));
        $TypeKuda = (int) $request->input('type_kuda');

        // --- Для проверки, что дата поездки в текущем месяце ---
        $monthStart = $newVisitDateTime->copy()->startOfMonth();
        $monthEnd = $newVisitDateTime->copy()->endOfMonth();

        // Загружаем оригинальный заказ
        $originalOrder = Order::findOrFail($orderId);
        
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
        
        
        // Проверка ограничения: не больше 2 поездок в день
        $existingTripsCount = Order::where('client_id', $originalOrder->client_id)
            ->whereDate('visit_data', $newVisitDateTime->toDateString()) // Сравниваем только дату
            ->whereNull('deleted_at') // Исключаем удаленные
            ->whereNull('cancelled_at') // Исключаем отмененные
            ->count();

        if ($existingTripsCount >= 2) {
            return response()->json(['success' => false, 'message' => 'Невозможно создать заказ: клиент уже имеет 2 поездки в этот день.'], 422);
        }

        
        
        $directionText = ($TypeKuda == 2) ? ' (обратный путь)' : '';
        $newOrderData['komment'] = "Копия заказа {$originalOrder->pz_nom} от {$originalOrder->pz_data->format('d.m.Y H:i')}" . $directionText
                ." Выполнена из календаря поездок ". now()->format('d.m.Y H:i');
        
        // Создаем новый заказ
        $newOrder = Order::create($newOrderData);

        // Здесь можно добавить начальный статус, если это делается автоматически
        // $this->orderService->addInitialStatus($newOrder); // Пример
        
        // Возвращаем успешный ответ (с сообщением, если оно было)
        $response = ['success' => true, 'message' => 'Заказ успешно создан.', 'order_id' => $newOrder->id];
        if ($message) {
            $response['message'] = $message;
        }

        return response()->json($response);

    } catch (\Exception $e) {
        \Log::error('Ошибка при копировании заказа: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Произошла ошибка при создании заказа.'], 500);
    }
}
    
}