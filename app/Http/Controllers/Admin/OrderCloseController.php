<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Services\OrderCloseBuilder; // 
use App\Services\OrderCloseService; // 
use App\Models\Order;
use Carbon\Carbon;



class OrderCloseController extends BaseController
{
    protected $queryBuilder;
    protected $orderService;


    public function __construct(OrderCloseBuilder $queryBuilder, OrderCloseService $orderService)
    {
        $this->queryBuilder = $queryBuilder;
        $this->orderService = $orderService;
        
    }

    public function index(Request $request)
    {
        // Собираем параметры фильтрации
        $sort = $request->get('sort', 'visit_data');
        $direction = $request->get('direction', 'asc');
        $urlParams = [
            'sort' => $sort,
            'direction' => $direction,
        ];

         // Устанавливаем фильтр по дате поездки по умолчанию - начало и конец текущего месяца
        if (!$request->has('visit_date_from')) {
            $request->merge(['visit_date_from' => Carbon::now()->startOfMonth()->toDateString()]);
        }
        if (!$request->has('visit_date_to')) {
            $request->merge(['visit_date_to' => Carbon::now()->endOfMonth()->toDateString()]);
        }

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

        return view('social-taxi-orders.close', compact(
            'orders',
            'sort',
            'direction',
            'urlParams',
            'taxis'
        ));
    }

    public function bulkClose(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => 'array',
            'order_ids.*' => 'integer|exists:orders,id',
            'visit_date_from' => 'required|date_format:Y-m-d',
            'visit_date_to' => 'required|date_format:Y-m-d',
            'taxi_id' => 'nullable|integer|exists:taxis,id',
        ]);

        $closedAt = now();
        $currentUser = auth()->user();
        $operatorInfo = $currentUser->name . ' (' . $currentUser->litera . ')';

        $query = Order::whereDate('visit_data', '>=', $validated['visit_date_from'])
            ->whereDate('visit_data', '<=', $validated['visit_date_to'])
            ->where('taxi_id', $validated['taxi_id'])
            ->whereHas('currentStatus', function ($q) {
                 $q->where('status_order_id', 2); // Передан в такси
            })
            ->whereNull('closed_at') // Только не закрытые
            ->whereNotNull('visit_data')
            ->whereNull('deleted_at')
            ->whereNull('cancelled_at')
            // Добавляем новые обязательные условия
            ->where('taxi_price', '>', 0)
            ->where('taxi_vozm', '>', 0);                    

        if (!empty($validated['order_ids'])) {
            $query->whereIn('id', $validated['order_ids']);
        }

        $orders = $query->get();

        $updatedCount = 0;
        $invalidDateCount = 0; // Счётчик заказов с некорректной датой
        foreach ($orders as $order) {
            // Проверяем, что дата закрытия > даты поездки (по дню)
            if ($closedAt->toDateString() > $order->visit_data->toDateString()) {
                $order->closed_at = $closedAt;
                
                // Формируем комментарий автоматически
                $comment = 'Закрытие заказа: оператор ' . $operatorInfo . ', ' . now()->format('d.m.Y H:i');
                if ($order->komment) {
                    $order->komment = $order->komment . "\n" . $comment;
                } else {
                    $order->komment = $comment;
                }
                
                $order->save();
                $updatedCount++;
            } else {
                 // Заказ не будет закрыт из-за даты
                $invalidDateCount++;
            }
        }
        $urlParams = $this->orderService->getUrlParams();
        
        // Формируем сообщение
        $message = "Заказы закрыты: {$updatedCount} шт.";
        if ($invalidDateCount > 0) {
            $message .= " Не закрыто из-за даты поездки: {$invalidDateCount} шт. (Дата закрытия должна быть позже даты поездки).";
            return redirect()->route('social-taxi-orders.close.index', $urlParams)->with('warning', $message); // Используем 'warning'
        }

        if ($updatedCount === 0) {
            $message = "Нет заказов для закрытия. "
            . ($invalidDateCount > 0 ? "Все {$invalidDateCount} заказов не могут быть закрыты из-за даты поездки." : "Все заказы уже закрыты или не подходят под условия (taxi_price > 0 и taxi_vozm > 0).");
            return redirect()->route('social-taxi-orders.close.index', $urlParams)->with('info', $message);
        }

        return redirect()->route('social-taxi-orders.close.index', $urlParams)
            ->with('success', $message);    
        
    }

    public function bulkUnset(Request $request)
    // отмена закрытия заказа
    {
        $validated = $request->validate([
            'order_ids' => 'array',
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
            } else {
                $order->komment = $comment;
            }
            
            
            $order->save();
            $updatedCount++;
        }
        $urlParams = $this->orderService->getUrlParams();
        return redirect()->route('social-taxi-orders.close.index', $urlParams)->with('success', "Заказы открыты: {$updatedCount} шт.");
    }
}