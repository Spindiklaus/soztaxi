<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Services\TaxiOrderService;
use App\Services\TaxiOrderBuilder;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TaxiOrdersExport;
use App\Models\Order;


class TaxiOrderController extends BaseController {

    protected $queryBuilder;
    protected $orderService;

    public function __construct(TaxiOrderBuilder $queryBuilder, TaxiOrderService $orderService) {
        $this->queryBuilder = $queryBuilder;
        $this->orderService = $orderService;
    }

    // Показать список заказов для передачи в такси
    public function index(Request $request) {
        \Log::info('Taxi orders index called', [
        'all_params' => $request->all(),
        'method' => $request->method()
        ]);
        
         $sort = $request->get('sort', 'visit_data');
        $direction = $request->get('direction', 'asc');
    
//    \Log::info('Taxi orders sort params', [
//        'sort' => $sort,
//        'direction' => $direction,
//        'all_params' => $request->all()
//    ]);

        // Устанавливаем фильтр по дате поездки по умолчанию - сегодня
        if (!$request->has('visit_date_from')) {
            $request->merge(['visit_date_from' => date('Y-m-d')]);
        }
        if (!$request->has('visit_date_to')) {
            $request->merge(['visit_date_to' => date('Y-m-d')]);
        }

        // Собираем параметры для передачи в шаблон
        $urlParams = $this->orderService->getUrlParams();
        
        // Получаем список активных такси для фильтра
        $taxis = \App\Models\Taxi::where('life', 1)->orderBy('name')->get();

        // Используем упрощенную логику для такси
        $query = $this->queryBuilder->build($request, false);
        $orders = $query->paginate(15)->appends($request->all());

        return view('social-taxi-orders.taxi', compact(
            'orders',
            'sort',
            'direction',
            'urlParams',
            'taxis'    
        ));
    }
    
    public function exportToTaxi(Request $request) {
    \Log::info('Export to taxi called', [
        'visit_date_from' => $request->get('visit_date_from'),
        'visit_date_to' => $request->get('visit_date_to'),
        'taxi_id' => $request->get('taxi_id'),
        'all_params' => $request->all()
    ]);
    
    // Определяем такси - берем из запроса или первый активный
    $taxiId = $request->get('taxi_id');
    $taxi = $taxiId ? \App\Models\Taxi::find($taxiId) : \App\Models\Taxi::where('life', 1)->first();
    
    // Используем ТОТ ЖЕ запрос, что и в index
    $query = $this->queryBuilder->build($request, false);
    
    // Получаем ВСЕ заказы (без пагинации)
    $orders = $query->get();
    
    \Log::info('Orders found for export', ['count' => $orders->count()]);
        
    // Формируем имя файла и передаем даты в экспорт
    $visitDateFrom = $request->get('visit_date_from', date('Y-m-d'));
    $visitDateTo = $request->get('visit_date_to', date('Y-m-d'));
    // Создаем Carbon объекты для форматирования
    $formattedDateFrom = \Carbon\Carbon::createFromFormat('Y-m-d', $visitDateFrom)->format('d.m.Y');
    $formattedDateTo = \Carbon\Carbon::createFromFormat('Y-m-d', $visitDateTo)->format('d.m.Y');
    $fileName = 'Сведения_для_передачи_оператору_такси_' . $visitDateFrom . '_по_' . $visitDateTo . '.xlsx';
    
    // Экспортируем - передаем все три аргумента!
    return Excel::download(new TaxiOrdersExport($orders, $formattedDateFrom, $formattedDateTo, $taxi), $fileName);
}

    public function setSentDate(Request $request) {
    try {
        // Валидация данных
        $validated = $request->validate([
            'taxi_sent_at' => 'required|date_format:Y-m-d\TH:i',
            'visit_date_from' => 'required|date_format:Y-m-d',
            'visit_date_to' => 'required|date_format:Y-m-d',
            'taxi_id' => 'nullable|integer|exists:taxis,id',
        ]);
        
        // Проверяем, что дата передачи меньше даты поездки
        $taxiSentAt = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $validated['taxi_sent_at']);
        $visitDateFrom = \Carbon\Carbon::parse($validated['visit_date_from']);
        
        if ($taxiSentAt >= $visitDateFrom) {
            return redirect()->back()->with('error', 'Дата передачи в такси должна быть меньше даты поездки (' . $visitDateFrom->format('d.m.Y') . ').');
        }
        
        // Строим запрос напрямую через Order::where() с правильной фильтрацией по датам
        $query = Order::whereDate('visit_data', '>=', $validated['visit_date_from'])
            ->whereDate('visit_data', '<=', $validated['visit_date_to'])
            ->whereDoesntHave('currentStatus', function ($q) {
                $q->where('status_order_id', 3); // Исключаем отмененные
            })
            ->whereNull('deleted_at')
            ->whereNull('cancelled_at')
            ->whereNull('taxi_sent_at'); // Только заказы без даты передачи в такси
        
        // Фильтрация по оператору такси
        if (!empty($validated['taxi_id'])) {
            $query->where('taxi_id', $validated['taxi_id']);
        }
        
        // Получаем количество заказов для обновления
        $ordersCount = $query->count();
       
        
        if ($ordersCount === 0) {
            return redirect()->back()->with('info', 'Нет заказов для обновления (у всех уже установлена дата передачи в такси).');
        }

        $orders = $query->get();        
        // Обновляем каждый заказ через модель (вызываются события Eloquent и Observer)
        foreach ($orders as $order) {
            $order->taxi_sent_at = $taxiSentAt;
            $order->save(); // Это вызовет Observer!
        }
        // Возвращаемся с параметрами фильтрации
        $urlParams = $this->orderService->getUrlParams();
        
        return redirect()->route('taxi-orders.index', $urlParams)
                        ->with('success', "Дата передачи в такси установлена для {$ordersCount} заказов.");
        
    } catch (\Exception $e) {
        \Log::error('Ошибка при установке даты передачи в такси', [
            'exception' => $e,
            'request_data' => $request->all()
        ]);
        
        return redirect()->back()->with('error', 'Ошибка при установке даты передачи в такси: ' . $e->getMessage());
    }
}


}
