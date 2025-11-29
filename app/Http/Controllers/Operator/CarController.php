<?php

// app/Http/Controllers/Operator/CarController.php
namespace App\Http\Controllers\Operator;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\SocialTaxiOrderService;
use App\Services\SocialTaxiOrderBuilder;

class CarController extends BaseController
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
        
        // Устанавливаем фильтр по типу заказа "Легковой автомобиль" (ID = 2) и по текущему пользователю
        if (!$request->has('filter_type_order')) {
            $request->merge(['filter_type_order' => 2]);
        }
        
        if (!$request->has('filter_user_id')) {
            $request->merge(['filter_user_id' => auth()->id()]);
        }

        // По умолчанию показываем ВСЕ записи (включая удаленные)
        $showDeletedParam = $request->get('show_deleted', '1');
        $withTrashed = match($showDeletedParam) {
            '0' => false,  // Только активные
            '1' => true,   // Все (включая удаленные)
            '2' => 'true', // Только удаленные
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
        session(['operator_current_type' => $request->get('type_order', 2)]);
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
}