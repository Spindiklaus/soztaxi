<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\SocialTaxiOrderService;
use App\Services\SocialTaxiOrderBuilder;

class TaxiOrderController extends BaseController
{
    protected $queryBuilder;
    protected $orderService;

    public function __construct(SocialTaxiOrderBuilder $queryBuilder, SocialTaxiOrderService $orderService)
    {
        $this->queryBuilder = $queryBuilder;
        $this->orderService = $orderService;
    }

    public function index(Request $request) {
        // Устанавливаем фильтр по статусу "принят" (ID = 1) по умолчанию
        if (!$request->has('status_order_id')) {
            $request->merge(['status_order_id' => 1]);
        }
        
        // Устанавливаем сортировку по дате поездки по возрастанию по умолчанию
        if (!$request->has('sort')) {
            $request->merge(['sort' => 'visit_data']);
        }
        if (!$request->has('direction')) {
            $request->merge(['direction' => 'asc']);
        }
        
        // Добавляем параметр для индикации, что это страница принятых заказов
        $request->merge(['accepted_only' => 1]);
        
        // Вызываем основной метод index из SocialTaxiOrderController
        $showDeleted = $request->get('show_deleted', '0');
        $sort = $request->get('sort', 'pz_data');
        $direction = $request->get('direction', 'desc');

        // Получаем список операторов для фильтра
        $operators = User::orderBy('name')->get();

        // Собираем параметры для передачи в шаблон
        $urlParams = $this->orderService->getUrlParams();

        $query = $this->queryBuilder->build($request, $showDeleted == '1');
        $orders = $query->paginate(15)->appends($request->all());

        return view('social-taxi-orders.taxi', compact(
            'orders',
            'showDeleted',
            'sort',
            'direction',
            'urlParams',
            'operators'
        ));
    }
}