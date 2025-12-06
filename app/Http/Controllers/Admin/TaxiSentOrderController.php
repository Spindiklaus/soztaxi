<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\UnsetTaxiSentDateRequest;
use App\Http\Requests\TransferPredictiveDataRequest;
use App\Services\TaxiSentOrderService;
use App\Services\TaxiSentOrderBuilder;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Taxi;
use Illuminate\Http\JsonResponse;

use App\Imports\TaxiExcelImport;

class TaxiSentOrderController extends BaseController {

    protected $queryBuilder;
    protected $orderService;

    public function __construct(TaxiSentOrderBuilder $queryBuilder, TaxiSentOrderService $orderService) {
        $this->queryBuilder = $queryBuilder;
        $this->orderService = $orderService;
    }

    // Показать список заказов для переданных в такси
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
//        $taxi_sent_at = now(); // дата передачи сведений в такси
        // Используем упрощенную логику для такси
        $query = $this->queryBuilder->build($request, false);
        $orders = $query->paginate(50)->appends($request->all());
        $totalOrders = $orders->total();

        return view('social-taxi-orders.taxi_sent', compact(
                        'orders',
                        'sort',
                        'direction',
                        'urlParams',
                        'taxis',
                        'totalOrders'
        ));
    }

    // отменить передачу сведений в такси
    public function unsetSentDate(UnsetTaxiSentDateRequest  $request) {
            // Валидация данных
            $validated = $request->validated();

             $ordersCount = $this->orderService->unsetSentDate($validated);

            if ($ordersCount === 0) {
                return redirect()->back()->with('info', 'Нет заказов для обновления (у всех уже снята дата передачи в такси).');
            }
           
            // Возвращаемся с параметрами фильтрации
            $urlParams = $this->orderService->getUrlParams();

            return redirect()->route('taxi_sent-orders.index', $urlParams)
                            ->with('success', "Дата передачи в такси снята для {$ordersCount} заказов. Не забудьте отправить сведения об отмене в такси!");
    }

    public function transferPredictiveData(TransferPredictiveDataRequest $request) {
        $validated = $request->validated();

        $updatedCount = $this->orderService->transferPredictiveData($validated);
        $urlParams = $this->orderService->getUrlParams();

        if ($updatedCount === 0) {
            return redirect()->route('taxi_sent-orders.index', $urlParams)->with('info', 'Нет заказов для обновления (нет заказов соцтакси со статусом "Передан в такси" с заполненной предварительной дальностью).');
        }

        return redirect()->route('taxi_sent-orders.index', $urlParams)
            ->with('success', "Предварительные данные перенесены в фактические для {$updatedCount} заказов.");
    }

   public function verifyExcel(Request $request)
{
    // Валидация загрузки файла
    $request->validate([
        'excel_file' => 'required|mimes:xlsx,xls',
        'date_from' => 'required|date',
        'date_to' => 'required|date',
    ]);
    
    try {
        // Читаем файл Excel
        $rows = Excel::toArray(new TaxiExcelImport(), $request->file('excel_file'));
        \Log::info('Excel file read successfully', ['rows_count' => count($rows)]);
    } catch (\Exception $e) {
        \Log::error('Error reading Excel file', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        return redirect()->back()->withErrors(['excel_file' => 'Ошибка при чтении файла Excel: ' . $e->getMessage()]);
    }

    $taxi = Taxi::where('life', 1)->first();

    // Берем первый (и единственный) лист
    $data = $rows[0];
    \Log::info('First sheet data loaded', ['data_rows_count' => count($data)]);

    // Пропускаем первые 4 строки (начиная с 5-й строки идут данные)
    for ($i = 0; $i < 4; $i++) {
        $shiftedRow = array_shift($data);
        \Log::debug("Skipped header row $i", ['content' => $shiftedRow]);
    }
    \Log::info('Header rows skipped, processing data rows', ['remaining_rows_count' => count($data)]);

    $results = [];
    $notFound = [];


    foreach ($data as $index => $row) {
        //  "№ заказа" - это колонка B (индекс 1), "Предв. дальность" - колонка H (индекс 7)
        $pz_nom = trim($row[1] ?? ''); // Колонка B - № заказа
        $predvWayFromFile = trim($row[7] ?? ''); // Колонка H - Предв. дальность
        $priceFromFile = trim($row[8] ?? ''); // Колонка I - Цена за поездку
        $sumToPayFromFile = trim($row[9] ?? ''); // Колонка J - Сумма к оплате
        $sumToReimburseFromFile = trim($row[10] ?? ''); // Колонка K - Сумма к возмещению
        
        \Log::debug("Processing row " . ($index + 5), ['order_number_raw' => $row[1], 'predv_way_raw' => $row[7]]); // Логируем строку, начиная с 5-й


        if (empty($pz_nom)) {
            \Log::debug("Skipping empty order number at row " . ($index + 5));
            continue; // Пропускаем пустые строки
        }

        \Log::debug("Searching for order number: " . $pz_nom);

        // Ищем заказ по номеру, удаленный заказ не должен быть найден. Отмененный - найдется
        $order = Order::where('pz_nom', $pz_nom)
            ->whereNull('taxi_way')
            ->with('currentStatus')
            ->first();

        if ($order) {
            \Log::debug("Order found: " . $order->id . ", predv_way_db: " . $order->predv_way);
            $results[] = [
                'pz_nom' => $pz_nom,
                'file_predv_way' => $predvWayFromFile,
                'file_price' => $priceFromFile,           // Цена за поездку из файла
                'file_sum_to_pay' => $sumToPayFromFile,    // Сумма к оплате из файла
                'file_sum_to_reimburse' => $sumToReimburseFromFile, // Сумма к возмещению из файла
                'db_predv_way' => $order->predv_way,
                'db_price' => number_format(calculateFullTripPrice($order, 11, $taxi), 11, ',', ' '),           // Цена за поездку из БД
                'db_sum_to_pay' => number_format(calculateClientPaymentAmount($order, 11, $taxi), 11, ',', ' '),    // Сумма к оплате из БД
                'db_sum_to_reimburse' => number_format(calculateReimbursementAmount($order, 11, $taxi), 11, ',', ' '), // Сумма к возмещению из БД
                'order_id' => $order->id,
                'status_name' => $order->currentStatus->statusOrder->name,      // наименование статуса
                'status_color' => $order->currentStatus->statusOrder->color, //цвет статуса
                'found' => true
            ];
        } else {
            \Log::debug("Order NOT found for number: " . $pz_nom);
            $notFound[] = [
                'pz_nom' => $pz_nom,
                'file_predv_way' => $predvWayFromFile,
                'found' => false
            ];
        }
        
        // Подсчитываем суммы
        $summary = [
            'file_predv_way' => 0,
            'file_price' => 0,
            'file_sum_to_pay' => 0,
            'file_sum_to_reimburse' => 0,
            'db_predv_way' => 0,
            'db_price' => 0,
            'db_sum_to_pay' => 0,
            'db_sum_to_reimburse' => 0,
        ];

        foreach ($results as $result) {
            $summary['file_predv_way'] += (float) $result['file_predv_way'];
            $summary['file_price'] += (float) $result['file_price'];
            $summary['file_sum_to_pay'] += (float) $result['file_sum_to_pay'];
            $summary['file_sum_to_reimburse'] += (float) $result['file_sum_to_reimburse'];
            $summary['db_predv_way'] += (float) $result['db_predv_way'];
            $summary['db_price'] += (float) $result['db_price'];
            $summary['db_sum_to_pay'] += (float) $result['db_sum_to_pay'];
            $summary['db_sum_to_reimburse'] += (float) $result['db_sum_to_reimburse'];
        }

        
        
        
    }
    
    \Log::info('Processing complete', ['results_count' => count($results), 'not_found_count' => count($notFound)]);


    // Передаем результаты в представление
    return view('social-taxi-orders.taxi_sent_verify', compact('results', 'notFound', 'request', 'summary'));
 }  
 
// public function updatePredvWay(Request $request)
//    {
//        // Валидация входящих данных
//        $request->validate([
//            'order_id' => 'required|exists:orders,id',
//            'new_predv_way' => 'required|numeric|min:0', // Предполагаем, что это число
//            'date_from' => 'required|date',
//            'date_to' => 'required|date',
//        ]);
//
//        $orderId = $request->input('order_id');
//        $newPredvWay = $request->input('new_predv_way');
//        // Получаем параметры для редиректа
//        $dateFrom = $request->input('date_from');
//        $dateTo = $request->input('date_to');
//
//        try {
//            // Найти заказ
//            $order = Order::findOrFail($orderId);
//
//            // Сохранить старое значение для комментария (опционально)
//            $oldPredvWay = $order->predv_way;
//
//            // Обновить поле predv_way
//            $order->predv_way = $newPredvWay;
//            $order->save();
//
//            // Добавить комментарий об изменении (опционально)
//            $comment = 'Предварительная дальность обновлена по файлу Excel: ' . $oldPredvWay . ' -> ' . $newPredvWay . 'км. Оператор: ' . auth()->user()->name . ' (' . auth()->user()->litera . ') ' . now()->format('d.m.Y H:i');
//            $order->komment = $order->komment ? $order->komment . "\n" . $comment : $comment;
//            $order->save(); // Сохранить комментарий
//
//            \Log::info("Updated predv_way for order ID {$orderId} from {$oldPredvWay} to {$newPredvWay} via Excel verification.");
//
//        } catch (\Exception $e) {
//            \Log::error("Error updating predv_way for order ID {$orderId}: " . $e->getMessage());
//            // Редиректим с ошибкой на index с параметрами
//            return redirect()->route('taxi_sent-orders.index', [
//                'date_from' => $dateFrom,
//                'date_to' => $dateTo,
//            ])->withErrors(['global' => 'Ошибка при обновлении данных заказа.']);
//        }
//
//        // Редирект на страницу списка переданных в такси с исходными параметрами
//        return redirect()->route('taxi_sent-orders.index', [
//            'date_from' => $dateFrom,
//            'date_to' => $dateTo,
//        ])->with('success', "Предварительная дальность для заказа №{$order->pz_nom} обновлена на {$newPredvWay}.");
//    }
    
    public function updatePredvWayAjax(Request $request): JsonResponse
        {
            // Валидация входящих данных
            $request->validate([
                'order_id' => 'required|exists:orders,id',
                'new_predv_way' => 'required|numeric|min:0', // Предполагаем, что это число
            ]);

            $orderId = $request->input('order_id');
            $newPredvWay = $request->input('new_predv_way');

            try {
                // Найти заказ
                $order = Order::findOrFail($orderId);

                // Сохранить старое значение для комментария (опционально)
                $oldPredvWay = $order->predv_way;

                // Обновить поле predv_way
                $order->predv_way = $newPredvWay;
                $order->save();

                // Добавить комментарий об изменении (опционально)
                $comment = 'Предварительная дальность обновлена по файлу Excel: ' . $oldPredvWay . ' -> ' . $newPredvWay . 'км. Оператор: ' . auth()->user()->name . ' (' . auth()->user()->litera . ') ' . now()->format('d.m.Y H:i');
                $order->komment = $order->komment ? $order->komment . "\n" . $comment : $comment;
                $order->save(); // Сохранить комментарий

                \Log::info("Updated predv_way for order ID {$orderId} from {$oldPredvWay} to {$newPredvWay} via Excel verification (AJAX).");

                // Возвращаем успешный JSON-ответ
                return response()->json([
                    'success' => true,
                    'message' => "Предварительная дальность для заказа №{$order->pz_nom} обновлена на {$newPredvWay}.",
                    'order_id' => $order->id,
                    'new_predv_way' => $newPredvWay, // Возвращаем новое значение, если нужно обновить UI
                    'old_predv_way' => $oldPredvWay, // Возвращаем старое значение, если нужно обновить UI
                ]);

            } catch (\Exception $e) {
                \Log::error("Error updating predv_way for order ID {$orderId} (AJAX): " . $e->getMessage());

                // Возвращаем JSON-ответ с ошибкой
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка при обновлении данных заказа: ' . $e->getMessage(),
                ], 500); // 500 Internal Server Error
            }
        }
    
 
}
