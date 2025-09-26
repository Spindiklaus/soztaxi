<?php

// app/Http/Controllers/Operator/GazelleController.php
namespace App\Http\Controllers\Operator;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\SocialTaxiOrderService;
use App\Services\SocialTaxiOrderBuilder;

class GazelleController extends BaseController
{
    protected $queryBuilder;
    protected $orderService;

    public function __construct(SocialTaxiOrderBuilder $queryBuilder, SocialTaxiOrderService $orderService)
    {
        $this->queryBuilder = $queryBuilder;
        $this->orderService = $orderService;
    }

    public function index(Request $request) {
        // Устанавливаем фильтр по типу заказа "ГАЗель" (ID = 3) и по текущему пользователю
        if (!$request->has('filter_type_order')) {
            $request->merge(['filter_type_order' => 3]);
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
        $orders = $query->paginate(15)->appends($request->all());
        
        // Сохраняем текущий тип заказа в сессии только для операторов
        session(['operator_current_type' => $request->get('type_order', 3)]);
        session(['from_operator_page' => true]);

        return view('operator-orders.index', compact(
            'orders',
            'showDeleted',
            'sort',
            'direction',
            'urlParams',
            'operators'
        ));
    }
}