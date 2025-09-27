<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Services\TaxiOrderService;
use App\Services\TaxiOrderBuilder;

class TaxiOrderController extends BaseController {

    protected $queryBuilder;
    protected $orderService;

    public function __construct(TaxiOrderBuilder $queryBuilder, TaxiOrderService $orderService) {
        $this->queryBuilder = $queryBuilder;
        $this->orderService = $orderService;
    }

    // Показать список заказов для передачи в такси
    public function index(Request $request) {
        $sort = $request->get('sort', 'visit_data');
        $direction = $request->get('direction', 'asc');

        // Устанавливаем фильтр по дате поездки по умолчанию - сегодня
        if (!$request->has('visit_date_from')) {
            $request->merge(['visit_date_from' => date('Y-m-d')]);
        }
        if (!$request->has('visit_date_to')) {
            $request->merge(['visit_date_to' => date('Y-m-d')]);
        }

        // Собираем параметры для передачи в шаблон
        $urlParams = $this->orderService->getUrlParams();

        // Используем упрощенную логику для такси
        $query = $this->queryBuilder->build($request, false);
        $orders = $query->paginate(15)->appends($request->all());

        return view('social-taxi-orders.taxi', compact(
            'orders',
            'sort',
            'direction',
            'urlParams'
        ));
    }

}
