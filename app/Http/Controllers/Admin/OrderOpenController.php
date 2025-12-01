<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Services\OrderOpenBuilder; 
use App\Services\OrderOpenService; 
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
        if (!$request->has('date_from')) {
            $request->merge(['date_from' => Carbon::now()->startOfMonth()->toDateString()]);
        }
        if (!$request->has('date_to')) {
            $request->merge(['date_to' => Carbon::now()->endOfMonth()->toDateString()]);
        }

        $urlParams = $this->orderService->getUrlParams();        
        // Используем билдер
        $query = $this->queryBuilder->build($request);
        $orders = $query->paginate(20)->appends($request->all());
        $totalOrders = $orders->total();

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
        $messages = [
        'order_ids.required' => 'Необходимо выбрать хотя бы один заказ для снятия отметки "Закрыт".',
        'order_ids.array' => 'Выбранные заказы должны быть представлены в виде массива.',
        'order_ids.min' => 'Необходимо выбрать хотя бы один заказ для снятия отметки "Закрыт".',
        'order_ids.*.integer' => 'ID заказа должен быть числом.',
        'order_ids.*.exists' => 'Один или несколько выбранных заказов не существуют в базе данных.',
        'date_from.required' => 'Поле "Дата от" обязательно для заполнения.',
        'date_from.date_format' => 'Поле "Дата от" должно быть в формате ГГГГ-ММ-ДД.',
        'date_to.required' => 'Поле "Дата до" обязательно для заполнения.',
        'date_to.date_format' => 'Поле "Дата до" должно быть в формате ГГГГ-ММ-ДД.',
        'taxi_id.integer' => 'ID оператора такси должен быть числом.',
        'taxi_id.exists' => 'Выбранный оператор такси не существует.',
        ];
        $validated = $request->validate([
            'order_ids' => 'required|array|min:1', // Обязательно, массив, минимум 1 элемент
            'order_ids.*' => 'integer|exists:orders,id',
            'date_from' => 'required|date_format:Y-m-d',
            'date_to' => 'required|date_format:Y-m-d',
            'taxi_id' => 'nullable|integer|exists:taxis,id',
        ], $messages);

        $currentUser = auth()->user();
        $operatorInfo = $currentUser->name . ' (' . $currentUser->litera . ')';

        $query = Order::whereDate('visit_data', '>=', $validated['date_from'])
                ->whereDate('visit_data', '<=', $validated['date_to'])
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
