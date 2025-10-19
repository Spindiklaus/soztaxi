<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Models\FioDtrn;
use Illuminate\Support\Facades\DB;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder; // Импортируем Builder, если нужно для whereHas


class FioDtrnController extends BaseController {

    public function index(Request $request) {
        
        $ripFilter = $request->input('rip', 0);
        
        $query = FioDtrn::query();

        if ($request->filled('fio')) {
            $query->where('fio', 'like', "%{$request->input('fio')}%");
        }

        if ($request->filled('kl_id')) {
            $query->where('kl_id', 'like', "%{$request->input('kl_id')}%");
        }

        if ($request->filled('sex')) {
            $query->where('sex', $request->input('sex'));
        }

        if ($ripFilter == 1) {
            $query->whereNotNull('rip_at');
        }
        
        // --- Фильтр по дате поездок ---
        $visitDateFrom = $request->input('visit_date_from');
        $visitDateTo = $request->input('visit_date_to');
        
        // Коллекция для хранения ID клиентов, у которых есть заказы в диапазоне
        $filteredClientIds = null;
        if ($visitDateFrom || $visitDateTo) {
            $orderQuery = Order::select('client_id'); // Ищем по client_id
            if ($visitDateFrom) {
                $orderQuery->whereDate('visit_data', '>=', $visitDateFrom);
            }
            if ($visitDateTo) {
                $orderQuery->whereDate('visit_data', '<=', $visitDateTo);
            }

            // Получаем уникальные ID клиентов из заказов
            $filteredClientIds = $orderQuery->pluck('client_id')->unique();
        }
        if ($filteredClientIds !== null) {
            // Если есть фильтр по дате, ограничиваем выборку клиентов
            $query->whereIn('id', $filteredClientIds);
        }
         // --- Подсчет заказов с учетом фильтра по дате ---
        // Если фильтр по дате применяется, нужно изменить, как считается orders_count
        // Лучше всего сделать это через отношения и скоупы, но для простоты, добавим условие в withCount
        $ordersCountQuery = Order::query();
        if ($visitDateFrom) {
            $ordersCountQuery->whereDate('visit_data', '>=', $visitDateFrom);
        }
        if ($visitDateTo) {
            $ordersCountQuery->whereDate('visit_data', '<=', $visitDateTo);
        }
        
        $query->withCount(['orders' => function ($q) use ($ordersCountQuery) {
            // Применяем те же условия к подсчету (без type_order)
            if ($ordersCountQuery->getQuery()->wheres) {
                $q->where(function ($subQ) use ($ordersCountQuery) {
                    foreach ($ordersCountQuery->getQuery()->wheres as $where) {
                        if ($where['type'] === 'Basic' && $where['column'] === 'visit_data') {
                            $subQ->{strtoupper($where['operator'])}('visit_data', $where['value']);
                        }
                    }
                });
            }
        }]);
 
        // Сортировка
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');

        $allowedSorts = ['id', 'fio', 'kl_id', 'data_r', 'sex', 'orders_count'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id';
        }
        if (!in_array(strtolower($direction), ['asc', 'desc'])) {
            $direction = 'asc';
        }
        

        $fiodtrns = $query->orderBy($sort, $direction)->paginate(50);
         // --- ДИАГНОСТИКА ---
        \Log::info('FioDtrn Index Paginated Results Count (items):', [$fiodtrns->count()]);
        \Log::info('FioDtrn Index Paginated Results Items:', $fiodtrns->items());
        // --- КОНЕЦ ДИАГНОСТИКИ ---
        
        // Подсчет дубликатов ФИО
        // ВАЖНО: Этот подсчет НЕ УЧИТЫВАЕТ фильтр по дате поездок!
        $duplicateCounts = FioDtrn::query()
            ->select('fio', DB::raw('COUNT(*) as count'))
            ->whereNull('rip_at')    
            ->groupBy('fio')
            ->having('count', '>', 1)
            ->orderBy('fio')    
            ->pluck('count', 'fio'); // ['Иванов Иван Иванович' => 3, ...]

        // Подготовим данные для Alpine.js
        $fiodtrnsJs = [];
        foreach ($fiodtrns as $fiodtrn) {
            $fiodtrnsJs[] = [
                'id' => $fiodtrn->id,
                'kl_id' => $fiodtrn->kl_id,
                'fio' => $fiodtrn->fio,
                'data_r' => optional($fiodtrn->data_r)->format('d.m.Y'),
                'sex' => $fiodtrn->sex,
                'rip_at' => optional($fiodtrn->rip_at)->format('d.m.Y'),
                'operator' => optional($fiodtrn->user)->name ?? '-',
                'komment' => $fiodtrn->komment,
                 'orders_count' => $fiodtrn->orders_count, // <-- количество заказов
            ];
        }
        
        // Передаем параметры фильтра и сортировки в шаблон
        $urlParams = [
            'sort' => $sort,
            'direction' => $direction,
            'fio' => $request->input('fio'),
            'kl_id' => $request->input('kl_id'),
            'sex' => $request->input('sex'),
            'rip' => $ripFilter,
            'visit_date_from' => $visitDateFrom,
            'visit_date_to' => $visitDateTo,
        ];


        return view('fiodtrns.index', compact('fiodtrns', 'sort', 'direction', 'fiodtrnsJs', 'duplicateCounts', 'urlParams', 'ripFilter'));
    }

    public function create(Request $request) {
        $fiodtrn = new FioDtrn();
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');
        // Получаем текущие параметры сортировки и фильтрации
        $urlParams = [
            'sort' => $request->input('sort', 'id'),
            'direction' => $request->input('direction', 'asc'),
            'fio' => $request->input('fio'),
            'kl_id' => $request->input('kl_id'),
            'sex' => $request->input('sex'),
            'rip' => $request->input('rip'),
            'visit_date_from' => $request->input('visit_date_from'),
            'visit_date_to' => $request->input('visit_date_to'),
        ];
        return view('fiodtrns.create', compact('fiodtrn', 'sort', 'direction', 'urlParams'));
    }

    public function store(Request $request) {
        $request->validate([
            'kl_id' => 'required|string|max:255|unique:fio_dtrns,kl_id',
            'fio' => 'required|string|max:255',
            'data_r' => 'nullable|date',
            'sex' => 'nullable|in:М,Ж',
        ]);

        // Автоматически добавляем текущего пользователя
        $request->merge(['user_id' => auth()->id()]);

        FioDtrn::create($request->all());
        
        // Передаем все параметры сортировки и фильтрации
        $urlParams = [
            'sort' => $request->input('sort', 'id'),
            'direction' => $request->input('direction', 'asc'),
            'fio' => $request->input('fio'),
            'kl_id' => $request->input('kl_id'),
            'sex' => $request->input('sex'),
            'rip' => $request->input('rip'),
            'visit_date_from' => $request->input('visit_date_from'),
            'visit_date_to' => $request->input('visit_date_to'),
        ];

        return redirect()->route('fiodtrns.index', [
                    'sort' => $request->input('sort', 'id'),
                    'direction' => $request->input('direction', 'asc'),
                    $urlParams
                ])->with('success', 'Клиент успешно создан');
    }

    public function show(Request $request, FioDtrn $fiodtrn) {
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');
        $urlParams = [
            'sort' => $request->input('sort', 'id'),
            'direction' => $request->input('direction', 'asc'),
            'fio' => $request->input('fio'),
            'kl_id' => $request->input('kl_id'),
            'sex' => $request->input('sex'),
            'rip' => $request->input('rip'),
            'visit_date_from' => $request->input('visit_date_from'),
            'visit_date_to' => $request->input('visit_date_to'),
        ];
        return view('fiodtrns.show', compact('fiodtrn', 'sort', 'direction', 'urlParams'));
    }

    public function edit(Request $request, FioDtrn $fiodtrn) {
        // Получаем всех пользователей с ролью admin или operator
        $users = \App\Models\User::whereHas('roles', function ($query) {
                    $query->whereIn('name', ['admin', 'operator']);
                })->get();

        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');
         // Получаем текущие параметры сортировки и фильтрации
        $urlParams = [
            'sort' => $request->input('sort', 'id'),
            'direction' => $request->input('direction', 'asc'),
            'fio' => $request->input('fio'),
            'kl_id' => $request->input('kl_id'),
            'sex' => $request->input('sex'),
            'rip' => $request->input('rip'),
            'visit_date_from' => $request->input('visit_date_from'),
            'visit_date_to' => $request->input('visit_date_to'),
        ];
        
        
        return view('fiodtrns.edit', compact('fiodtrn', 'sort', 'direction', 'users', 'urlParams'));
    }

    public function update(Request $request, FioDtrn $fiodtrn) {

//        $data = $request->all();
//        dd($data['created_rip']);
//        // Замена T на пробел, чтобы соответствовало формату Y-m-d H:i
//        if (!empty($data['created_rip'])) {
//            $data['created_rip'] = str_replace('T', ' ', $data['created_rip']);
//        }
////        dd($data['created_rip']);
//        $request->replace($data);
        
        $request->validate([
            'kl_id' => 'required|string|max:255|unique:fio_dtrns,kl_id,' . $fiodtrn->id,
            'fio' => 'required|string|max:255',
            'data_r' => 'nullable|date',
            'sex' => 'nullable|in:М,Ж',
            'rip_at' => 'nullable|date',
            'created_rip' => 'nullable|required_with:rip_at|date_format:Y-m-d\TH:i',
            'user_rip' => 'nullable|required_with:rip_at|exists:users,id',
            'komment' => 'nullable|required_with:rip_at|string',
        ]);

        $fiodtrn->update($request->all());
        
        // Передаем все параметры сортировки и фильтрации
        $urlParams = [
            'sort' => $request->input('sort', 'id'),
            'direction' => $request->input('direction', 'asc'),
            'fio' => $request->input('fio'),
            'kl_id' => $request->input('kl_id'),
            'sex' => $request->input('sex'),
            'rip' => $request->input('rip'),
            'visit_date_from' => $request->input('visit_date_from'),
            'visit_date_to' => $request->input('visit_date_to'),
        ];

        return redirect()->route('fiodtrns.index', $urlParams) // <-- Передаем $urlParams как есть
                        ->with('success', "Клиент {$fiodtrn->fio} обновлён");
    }

    public function destroy(Request $request, FioDtrn $fiodtrn) {
        $fiodtrn->delete();
        
        // Передаем все параметры сортировки и фильтрации
        $urlParams = [
            'sort' => $request->input('sort', 'id'),
            'direction' => $request->input('direction', 'asc'),
            'fio' => $request->input('fio'),
            'kl_id' => $request->input('kl_id'),
            'sex' => $request->input('sex'),
            'rip' => $request->input('rip'),
            'visit_date_from' => $request->input('visit_date_from'),
            'visit_date_to' => $request->input('visit_date_to'),
        ];

        return redirect()->route('fiodtrns.index', $urlParams)->with('success', 'Клиент удалён');
    }

}
