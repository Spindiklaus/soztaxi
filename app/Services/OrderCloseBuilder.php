<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class OrderCloseBuilder
{
    public function build(Request $request): Builder
    {
        $query = Order::with(['currentStatus.statusOrder', 'client', 'category', 'dopus', 'taxi', 'user']);

        // Фильтрация по дате поездки
        $DateFrom = $request->get('date_from');
        $DateTo = $request->get('date_to');

        if ($DateFrom) {
            $query->whereDate('visit_data', '>=', $DateFrom);
        }
        if ($DateTo) {
            $query->whereDate('visit_data', '<=', $DateTo);
        }

        // Фильтрация по оператору такси
        if ($request->filled('taxi_id')) {
            $query->where('taxi_id', $request->input('taxi_id'));
        }

        // Фильтрация по типу заказа
        if ($request->filled('filter_type_order')) {
            $query->where('type_order', $request->input('filter_type_order'));
        }

        // Фильтрация по ФИО клиента
        if ($request->filled('client_fio')) {
            $query->whereHas('client', function ($q) use ($request) {
                $q->where('fio', 'like', '%' . $request->input('client_fio') . '%');
            });
        }

        // Применяем условия для "закрытия"
        $query = $query
            ->whereHas('currentStatus', function ($q) {
                $q->whereIn('status_order_id', [2,4]); // Передан в такси ил закрыт
            })
            ->whereNotNull('visit_data') // Есть дата поездки
            ->whereNull('deleted_at') // Не удален
            ->whereNull('cancelled_at'); // Не отменен

        // Сортировка
        $sort = $request->get('sort', 'visit_data');
        $direction = $request->get('direction', 'asc');
        switch ($sort) {
            case 'client_fio':
                // Сортировка по ФИО клиента из связанной таблицы
            
                $query->join('fio_dtrns', 'orders.client_id', '=', 'fio_dtrns.id') // Присоединяем таблицу fio_dtrns
                    ->select('orders.*', 'fio_dtrns.fio as client_fio')
                    ->orderBy('client_fio', $direction);           
                break;
            case 'pz_data':
                $query->orderBy('pz_data', $direction);
                break;
            case 'visit_data':
                $query->orderBy('visit_data', $direction);
                break;
            // Можно добавить другие поля, если они используются для сортировки
            default:
                // Если поле сортировки неизвестно, можно использовать сортировку по умолчанию или просто игнорировать
                // В данном случае, если поле неизвестно, будет сортировка по visit_data
                $query->orderBy('visit_data', $direction);
                break;
        }

        return $query;
    }
}