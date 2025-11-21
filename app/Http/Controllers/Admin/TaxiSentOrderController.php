<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\UnsetTaxiSentDateRequest;
use App\Http\Requests\TransferPredictiveDataRequest;
use App\Services\TaxiSentOrderService;
use App\Services\TaxiSentOrderBuilder;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TaxiOrdersExport;
use Carbon\Carbon;


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
        $orders = $query->paginate(15)->appends($request->all());

        return view('social-taxi-orders.taxi_sent', compact(
                        'orders',
                        'sort',
                        'direction',
                        'urlParams',
                        'taxis'
        ));
    }

    // В TaxiOrderController
    public function unsetSentDate(UnsetTaxiSentDateRequest  $request) {
            // Валидация данных
            $validated = $request->validated();

             $ordersCount = $this->orderService->unsetSentDate($validated);

            if ($ordersCount === 0) {
                return redirect()->back()->with('info', 'Нет заказов для обновления (у всех уже снята дата передачи в такси).');
            }
           
            // Возвращаемся с параметрами фильтрации
            $urlParams = $this->orderService->getUrlParams();

            return redirect()->route('taxi-orders.index', $urlParams)
                            ->with('success', "Дата передачи в такси снята для {$ordersCount} заказов. Не забудьте отправить сведения об отмене в такси!");
    }

    public function transferPredictiveData(TransferPredictiveDataRequest $request) {
        $validated = $request->validated();

        $updatedCount = $this->orderService->transferPredictiveData($validated);
        $urlParams = $this->orderService->getUrlParams();

        if ($updatedCount === 0) {
            return redirect()->route('taxi-orders.index', $urlParams)->with('info', 'Нет заказов для обновления (нет заказов соцтакси со статусом "Передан в такси" с заполненной предварительной дальностью).');
        }

        return redirect()->route('taxi-orders.index', $urlParams)
            ->with('success', "Предварительные данные перенесены в фактические для {$updatedCount} заказов.");
    }

}
