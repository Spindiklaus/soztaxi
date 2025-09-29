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
        $taxi_sent_at = now(); // дата передачи сведений в такси
        // Используем упрощенную логику для такси
        $query = $this->queryBuilder->build($request, false);
        $orders = $query->paginate(15)->appends($request->all());

        return view('social-taxi-orders.taxi', compact(
                        'orders',
                        'sort',
                        'direction',
                        'urlParams',
                        'taxis',
                        'taxi_sent_at'
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
                        $q->whereIn('status_order_id', [3, 4]); // Исключаем отмененные и закрытые
                    })
                    ->where('taxi_id', $validated['taxi_id'])
                    ->whereNull('deleted_at')
                    ->whereNull('cancelled_at')
                    ->whereNull('taxi_sent_at'); // Только заказы без даты передачи в такси
            // Отладка: выводим SQL запрос
            \Log::info('Taxi export query SQL', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

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

// В TaxiOrderController
    public function unsetSentDate(Request $request) {
        try {
            // Валидация данных
            $validated = $request->validate([
                'visit_date_from' => 'required|date_format:Y-m-d',
                'visit_date_to' => 'required|date_format:Y-m-d',
                'taxi_id' => 'nullable|integer|exists:taxis,id',
            ]);

            // Строим запрос напрямую через Order::where() с правильной фильтрацией по датам
            $query = Order::whereDate('visit_data', '>=', $validated['visit_date_from'])
                    ->whereDate('visit_data', '<=', $validated['visit_date_to'])
                    ->whereDoesntHave('currentStatus', function ($q) {
                        $q->whereIn('status_order_id', [3, 4]); // Исключаем отмененные и закрытые
                    })
                    ->where('taxi_id', $validated['taxi_id'])
                    ->whereNull('deleted_at')
                    ->whereNull('cancelled_at')
                    ->whereNotNull('taxi_sent_at'); // Только заказы с установленной датой передачи в такси
            // Получаем количество заказов для обновления
            $ordersCount = $query->count();

            if ($ordersCount === 0) {
                return redirect()->back()->with('info', 'Нет заказов для обновления (у всех уже снята дата передачи в такси).');
            }

            // Получаем заказы для обновления
            $orders = $query->get();
            // Обновляем каждый заказ через модель (вызываются события Eloquent и Observer)
            foreach ($orders as $order) {
                // Снимаем дату передачи в такси
                $order->taxi_sent_at = null;

                // Устанавливаем флаг отмены в такси
                $order->otmena_taxi = 1;

                // Добавляем комментарий об отмене передачи в такси
                $cancelTaxiComment = 'Отмена передачи в такси: сведения об отмене переданы оператором ' .
                        auth()->user()->name . ' (' . auth()->user()->litera . ')' .
                        ' ' . now()->format('d.m.Y H:i');

                if ($order->komment) {
                    $order->komment = $order->komment . "\n" . $cancelTaxiComment;
                } else {
                    $order->komment = $cancelTaxiComment;
                }

                $order->save(); // Это вызовет Observer!
            }

            // Возвращаемся с параметрами фильтрации
            $urlParams = $this->orderService->getUrlParams();

            return redirect()->route('taxi-orders.index', $urlParams)
                            ->with('success', "Дата передачи в такси снята для {$ordersCount} заказов. Не забудьте отправить сведения об отмене в такси!");
        } catch (\Exception $e) {
            \Log::error('Ошибка при снятии даты передачи в такси', [
                'exception' => $e,
                'request_data' => $request->all()
            ]);

            return redirect()->back()->with('error', 'Ошибка при снятии даты передачи в такси: ' . $e->getMessage());
        }
    }

    public function transferPredictiveData(Request $request) {
        try {
            // Валидация данных
            $validated = $request->validate([
                'visit_date_from' => 'required|date_format:Y-m-d',
                'visit_date_to' => 'required|date_format:Y-m-d',
                'taxi_id' => 'nullable|integer|exists:taxis,id',
            ]);

            // Строим запрос для поиска заказов соцтакси со статусом "Передан в такси" и заполненной предварительной дальностью
            $query =  Order::whereDate('visit_data', '>=', $validated['visit_date_from'])
                    ->whereDate('visit_data', '<=', $validated['visit_date_to'])
                    ->where('type_order', 1) // Только соцтакси
                    ->whereHas('currentStatus', function ($q) {
                        $q->where('status_order_id', 2); // Статус "Передан в такси"
                    })
                    ->where('taxi_id', $validated['taxi_id'])
                    ->whereNotNull('predv_way') // С предварительной дальностью
                    ->where('predv_way', '>', 0) // Больше 0
                    ->whereNull('deleted_at')
                    ->whereNull('cancelled_at');
            // Получаем заказы для обновления
            $orders = $query->get();
            $ordersCount = $orders->count();

            if ($ordersCount === 0) {
                return redirect()->back()->with('info', 'Нет заказов для обновления (нет заказов соцтакси со статусом "Передан в такси" с заполненной предварительной дальностью).');
            }

            // Получаем такси для расчетов
            $taxi = \App\Models\Taxi::find($validated['taxi_id']);

            // Обновляем каждый заказ
            $updatedCount = 0;
            foreach ($orders as $order) {
                try {
                    // Рассчитываем фактические значения
                    $taxiWay = $order->predv_way;
                    $taxiPrice = calculateTripPriceWithPickup($order, 11, $taxi);
                    $taxiVozm = calculateReimbursementAmount($order, 11, $taxi);

                    // Обновляем заказ
                    $order->taxi_way = $taxiWay;
                    $order->taxi_price = $taxiPrice;
                    $order->taxi_vozm = $taxiVozm;

                    // Добавляем комментарий
                    $comment = 'Перенос предварительных данных в фактические: ' .
                            'дальность ' . number_format($taxiWay, 11, '.', '') . ' км, ' .
                            'цена ' . number_format($taxiPrice, 11, '.', '') . ' руб., ' .
                            'возмещение ' . number_format($taxiVozm, 11, '.', '') . ' руб. ' .
                            'Оператор: ' . auth()->user()->name . ' (' . auth()->user()->litera . ') ' .
                            'дата: ' . now()->format('d.m.Y H:i');

                    if ($order->komment) {
                        $order->komment .= "\n" . $comment;
                    } else {
                        $order->komment = $comment;
                    }

                    $order->save();
                    $updatedCount++;
                } catch (\Exception $e) {
                    \Log::error('Ошибка при переносе данных для заказа ' . $order->id, [
                        'exception' => $e,
                        'order_id' => $order->id
                    ]);
                    continue; // Продолжаем с другими заказами
                }
            }

            if ($updatedCount === 0) {
                return redirect()->back()->with('error', 'Не удалось обновить ни один заказ. Проверьте логи.');
            }

            // Возвращаемся с параметрами фильтрации
            $urlParams = $this->orderService->getUrlParams();

            return redirect()->route('taxi-orders.index', $urlParams)
                            ->with('success', "Предварительные данные перенесены в фактические для {$updatedCount} заказов.");
        } catch (\Exception $e) {
            \Log::error('Ошибка при переносе предварительных данных в фактические', [
                'exception' => $e,
                'request_data' => $request->all()
            ]);

            return redirect()->back()->with('error', 'Ошибка при переносе предварительных данных в фактические: ' . $e->getMessage());
        }
    }

}
