<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class OrderOpenBuilder
{
    public function build(Request $request): Builder
    {
        $query = Order::with(['currentStatus.statusOrder', 'client', 'category', 'dopus', 'taxi', 'user']);

        // Фильтрация по дате поездки
        $visitDateFrom = $request->get('visit_date_from');
        $visitDateTo = $request->get('visit_date_to');

        if ($visitDateFrom) {
            $query->whereDate('visit_data', '>=', $visitDateFrom);
        }
        if ($visitDateTo) {
            $query->whereDate('visit_data', '<=', $visitDateTo);
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

        // Применяем условия для "открытия" - только закрытые заказы
        $query = $query
            ->whereNotNull('closed_at') // Только закрытые
//            ->whereHas('currentStatus', function ($q) {
//                $q->where('status_order_id', 4); // Закрыт
//            })
            ->whereNotNull('visit_data') // Есть дата поездки
            ->whereNull('deleted_at') // Не удален
            ->whereNull('cancelled_at'); // Не отменен

        // Сортировка
        $sort = $request->get('sort', 'visit_data');
        $direction = $request->get('direction', 'asc');
        $query->orderBy($sort, $direction);

        return $query;
    }
}
