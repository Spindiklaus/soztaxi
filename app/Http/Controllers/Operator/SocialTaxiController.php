<?php

// app/Http/Controllers/Operator/SocialTaxiController.php
namespace App\Http\Controllers\Operator;

use App\Models\User;
use App\Models\FioDtrn; 
use Illuminate\Http\Request;
use App\Services\SocialTaxiOrderService;
use App\Services\SocialTaxiOrderBuilder;

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
    $calendarMonth = $targetDate->startOfMonth();
    // --- КОНЕЦ ОПРЕДЕЛЕНИЯ МЕСЯЦА ---

    // --- ФИЛЬТРАЦИЯ ЗАКАЗОВ ЗА ВЫБРАННЫЙ МЕСЯЦ ---
    // Временно добавляем фильтр по диапазону дат для получения заказов за конкретный месяц
    $request->merge([
        'date_from' => $calendarMonth->format('Y-m-d'),
        'date_to' => $calendarMonth->endOfMonth()->format('Y-m-d'),
    ]);
    // --- КОНЕЦ ФИЛЬТРАЦИИ ---

    // Получаем заказы, отфильтрованные по клиенту И по месяцу
    $query = $this->queryBuilder->build($request, $showDeleted == '1');
    $orders = $query->paginate(50)->appends($request->all());

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
    
    
    
}