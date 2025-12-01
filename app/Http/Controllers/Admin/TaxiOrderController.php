<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\UpdateTaxiSentDateRequest;
use App\Services\TaxiOrderService;
use App\Services\TaxiOrderBuilder;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TaxiOrdersExport;
use Carbon\Carbon;


class TaxiOrderController extends BaseController {

    protected $queryBuilder;
    protected $orderService;

    public function __construct(TaxiOrderBuilder $queryBuilder, TaxiOrderService $orderService) {
        $this->queryBuilder = $queryBuilder;
        $this->orderService = $orderService;
    }

    // Показать список заказов для передачи в такси
    public function index(Request $request) {
//        \Log::info('Taxi orders index called', [
//            'all_params' => $request->all(),
//            'method' => $request->method()
//        ]);

        $sort = $request->get('sort', 'visit_data');
        $direction = $request->get('direction', 'asc');

//    \Log::info('Taxi orders sort params', [
//        'sort' => $sort,
//        'direction' => $direction,
//        'all_params' => $request->all()
//    ]);
        // Устанавливаем фильтр по дате поездки по умолчанию - начало и конец текущего месяца
        if (!$request->has('date_from')) {
            $request->merge(['date_from' => Carbon::now()->startOfMonth()->toDateString()]);
        }
        if (!$request->has('date_to')) {
            $request->merge(['date_to' => Carbon::now()->endOfMonth()->toDateString()]);
        }

        // Собираем параметры для передачи в шаблон
        $urlParams = $this->orderService->getUrlParams();

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
        $taxi_sent_at = now(); // дата передачи сведений в такси
        // Используем упрощенную логику для такси
        $query = $this->queryBuilder->build($request, false);
        $orders = $query->paginate(20)->appends($request->all());
        $totalOrders = $orders->total();

        return view('social-taxi-orders.taxi', compact(
                        'orders',
                        'sort',
                        'direction',
                        'urlParams',
                        'taxis',
                        'taxi_sent_at',
                        'totalOrders'
        ));
    }

    public function exportToTaxi(Request $request) {
//        \Log::info('Export to taxi called', [
//            'date_from' => $request->get('date_from'),
//            'date_to' => $request->get('date_to'),
//            'taxi_id' => $request->get('taxi_id'),
//            'all_params' => $request->all()
//        ]);

        // Определяем такси - берем из запроса или первый активный
        $taxiId = $request->get('taxi_id');
        $taxi = $taxiId ? \App\Models\Taxi::find($taxiId) : \App\Models\Taxi::where('life', 1)->first();

        // Используем ТОТ ЖЕ запрос, что и в index
        $request->merge(['sort' => 'visit_data', 'direction' => 'asc']);
        
        $query = $this->queryBuilder->build($request, false);
        
        // Получаем ВСЕ заказы (без пагинации) и фильтруем только соцтакси
        $orders = $query->get()->where('type_order', 1); // 
        
        $sortedOrders = $orders->sortBy([
            ['order_group_id', 'asc'],  // Группы первыми (NULL = одиночные — в конце)
            ['visit_data', 'asc']       // Внутри группы — по времени
        ])->values();
        

//        \Log::info('Orders found for export', ['count' => $orders->count()]);

        // Формируем имя файла и передаем даты в экспорт
        $DateFrom = $request->get('date_from', date('Y-m-d'));
        $DateTo = $request->get('date_to', date('Y-m-d'));
        // Создаем Carbon объекты для форматирования
        $formattedDateFrom = \Carbon\Carbon::createFromFormat('Y-m-d', $DateFrom)->format('d.m.Y');
        $formattedDateTo = \Carbon\Carbon::createFromFormat('Y-m-d', $DateTo)->format('d.m.Y');
        $fileName = 'Сведения_для_передачи_оператору_такси_' . $DateFrom . '_по_' . $DateTo . '.xlsx';

        // Экспортируем - передаем все три аргумента!
        return Excel::download(new TaxiOrdersExport($sortedOrders, $formattedDateFrom, $formattedDateTo, $taxi), $fileName);
    }
    
    /**
     * Сортировка заказов: сначала сгруппированные, затем одиночные
     */
//    private function sortOrdersWithGroups($orders)
//    {
//        // Разделяем заказы на сгруппированные и одиночные
//        $groupedOrders = collect();
//        $singleOrders = collect();
//        
//        foreach ($orders as $order) {
//            if ($order->order_group_id) {
//                $groupedOrders->push($order);
//            } else {
//                $singleOrders->push($order);
//            }
//        }
//        
//        // Сортируем сгруппированные заказы: сначала по группе, затем по времени
//        $sortedGrouped = $groupedOrders->sortBy([
//            ['order_group_id', 'asc'],
//            ['visit_data', 'asc']
//        ])->values();
//        
//        // Сортируем одиночные заказы по времени
//        $sortedSingles = $singleOrders->sortBy('visit_data')->values();
//        
//        // Объединяем: сначала сгруппированные, затем одиночные
//        return $sortedGrouped->concat($sortedSingles);
//    }
    
    

    public function setSentDate(UpdateTaxiSentDateRequest $request) {
            $validated = $request->validated();

            // Проверяем, что дата передачи меньше даты поездки
            $taxiSentAt = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $validated['taxi_sent_at']);
            $DateFrom = \Carbon\Carbon::parse($validated['date_from']);

            if ($taxiSentAt >= $DateFrom) {
                return redirect()->back()->with('error', 'Дата передачи в такси '.$taxiSentAt->format('d.m.Y') .' должна быть меньше даты фильтра '
                        . $DateFrom->format('d.m.Y') . '.');
            }
           
            // Получаем количество заказов для обновления
            $ordersCount = $this->orderService->setSentDate($validated, $taxiSentAt);

            if ($ordersCount === 0) {
                return redirect()->back()->with('info', 'Нет заказов для обновления (у всех уже установлена дата передачи в такси).');
            }
            
            // Возвращаемся с параметрами фильтрации
            $urlParams = $this->orderService->getUrlParams();

            return redirect()->route('taxi-orders.index', $urlParams)
                            ->with('success', "Дата передачи в такси установлена для {$ordersCount} заказов.");
    }

//     public function unsetSentDate(UnsetTaxiSentDateRequest  $request) {
//            // Валидация данных
//            $validated = $request->validated();
//
//             $ordersCount = $this->orderService->unsetSentDate($validated);
//
//            if ($ordersCount === 0) {
//                return redirect()->back()->with('info', 'Нет заказов для обновления (у всех уже снята дата передачи в такси).');
//            }
//           
//            // Возвращаемся с параметрами фильтрации
//            $urlParams = $this->orderService->getUrlParams();
//
//            return redirect()->route('taxi-orders.index', $urlParams)
//                            ->with('success', "Дата передачи в такси снята для {$ordersCount} заказов. Не забудьте отправить сведения об отмене в такси!");
//    }

//    public function transferPredictiveData(TransferPredictiveDataRequest $request) {
//        $validated = $request->validated();
//
//        $updatedCount = $this->orderService->transferPredictiveData($validated);
//        $urlParams = $this->orderService->getUrlParams();
//
//        if ($updatedCount === 0) {
//            return redirect()->route('taxi-orders.index', $urlParams)->with('info', 'Нет заказов для обновления (нет заказов соцтакси со статусом "Передан в такси" с заполненной предварительной дальностью).');
//        }
//
//        return redirect()->route('taxi-orders.index', $urlParams)
//            ->with('success', "Предварительные данные перенесены в фактические для {$updatedCount} заказов.");
//    }

}
