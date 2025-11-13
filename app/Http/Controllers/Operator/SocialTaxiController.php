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

    // Фильтрация по клиенту через связь с моделью Order
    // В SocialTaxiOrderBuilder нужно будет добавить обработку этого параметра
    if (!$request->has('filter_client_id')) {
        $request->merge(['filter_client_id' => $client->id]);
    }

    $showDeleted = $request->get('show_deleted', '0');
    $sort = $request->get('sort', 'visit_data'); // Сортировка по дате поездки
    $direction = $request->get('direction', 'asc');

    // Получаем заказы, отфильтрованные по клиенту
    $query = $this->queryBuilder->build($request, $showDeleted == '1');
    $orders = $query->paginate(50)->appends($request->all());

    // Подготовка данных для календаря
    $targetDate = null;
    if ($date) {
        $targetDate = \Carbon\Carbon::parse($date);
    } else {
        $targetDate - now();
    }
    

    // Определяем начальную и конечную даты для построения календаря
    // Учитываем только заказы, отфильтрованные выше
    $startDate = collect($calendarData)->keys()->min() ? now()->parse(collect($calendarData)->keys()->min())->startOfMonth() : now()->startOfMonth();
    $endDate = collect($calendarData)->keys()->max() ? now()->parse(collect($calendarData)->keys()->max())->endOfMonth() : now()->endOfMonth();

    // Собираем параметры URL для передачи в шаблон и обратной навигации
    $urlParams = $this->orderService->getUrlParams(); // Убедитесь, что этот метод возвращает массив параметров

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
        'urlParams' // Передаем параметры
    ));
}
    
    
    
}