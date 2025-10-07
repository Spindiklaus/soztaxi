<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Services\OrderOpenBuilder; // Создадим позже
use App\Services\OrderOpenService; // Создадим позже
use App\Models\Order;
use Carbon\Carbon;

class OrderOpenController extends BaseController {

    protected $queryBuilder;
    protected $orderService;

    public function __construct(OrderOpenBuilder $queryBuilder, OrderOpenService $orderService) {
        $this->queryBuilder = $queryBuilder;
        $this->orderService = $orderService;
    }

    public function index(Request $request) {
        // Собираем параметры фильтрации
        $sort = $request->get('sort', 'visit_data');
        $direction = $request->get('direction', 'asc');
        
        // Устанавливаем фильтр по дате поездки по умолчанию - начало и конец текущего месяца
        if (!$request->has('visit_date_from')) {
            $request->merge(['visit_date_from' => Carbon::now()->startOfMonth()->toDateString()]);
        }
        if (!$request->has('visit_date_to')) {
            $request->merge(['visit_date_to' => Carbon::now()->endOfMonth()->toDateString()]);
        }

        $urlParams = $this->orderService->getUrlParams();        
        // Используем билдер
        $query = $this->queryBuilder->build($request);
        $orders = $query->paginate(15)->appends($request->all());

        // Получаем список активных такси для фильтра
        $taxis = \App\Models\Taxi::where('life', 1)->orderBy('name')->get();
        if (!$request->has('taxi_id')) {
            $firstTaxi = $taxis->first();
            if ($firstTaxi) {
                $request->merge(['taxi_id' => $firstTaxi->id]);
                // Обновляем urlParams, чтобы ссылки пагинации/сортировки тоже вели к выбранному такси
                $urlParams['taxi_id'] = $firstTaxi->id;
            }
        }

        return view('social-taxi-orders.open', compact(
                        'orders',
                        'sort',
                        'direction',
                        'urlParams',
                        'taxis'
        ));
    }

    public function bulkUnset(Request $request) {
        // отмена закрытия заказа
        $validated = $request->validate([
            'order_ids' => 'required|array|min:1', // Обязательно, массив, минимум 1 элемент
            'order_ids.*' => 'integer|exists:orders,id',
            'visit_date_from' => 'required|date_format:Y-m-d',
            'visit_date_to' => 'required|date_format:Y-m-d',
            'taxi_id' => 'nullable|integer|exists:taxis,id',
        ]);

        $currentUser = auth()->user();
        $operatorInfo = $currentUser->name . ' (' . $currentUser->litera . ')';

        $query = Order::whereDate('visit_data', '>=', $validated['visit_date_from'])
                ->whereDate('visit_data', '<=', $validated['visit_date_to'])
                ->where('taxi_id', $validated['taxi_id'])
                ->whereNotNull('closed_at') // Только закрытые
                ->whereHas('currentStatus', function ($q) {
                    $q->where('status_order_id', 4); // Закрыт
                });

        if (!empty($validated['order_ids'])) {
            $query->whereIn('id', $validated['order_ids']);
        }

        $orders = $query->get();

        $updatedCount = 0;
        foreach ($orders as $order) {
            $order->closed_at = null;

            // Формируем комментарий об открытии автоматически
            $comment = 'Отмена закрытия заказа: оператор ' . $operatorInfo . ', ' . now()->format('d.m.Y H:i');

            if ($order->komment) {
                $order->komment = $order->komment . "\n" . $comment;
            }
            else {
                $order->komment = $comment;
            }

            $order->save();
            $updatedCount++;
        }
        $urlParams = $this->orderService->getUrlParams();
        return redirect()->route('social-taxi-orders.open.index', $urlParams)->with('success', "Заказы открыты: {$updatedCount} шт.");
    }
}
