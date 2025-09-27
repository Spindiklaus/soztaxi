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
    
    public function exportToTaxi(Request $request) {
    \Log::info('Export to taxi called', [
        'visit_date_from' => $request->get('visit_date_from'),
        'visit_date_to' => $request->get('visit_date_to'),
        'all_params' => $request->all()
    ]);
    
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
    return Excel::download(new TaxiOrdersExport($orders, $formattedDateFrom, $formattedDateTo), $fileName);
}

}
