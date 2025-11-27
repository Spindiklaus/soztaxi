<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\FioDtrn;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Services\FioDtrnService;
use Illuminate\Database\Eloquent\Builder; // Импортируем Builder, если нужно для whereHas

class FioDtrnController extends BaseController {

    protected $fioDtrnService; // бизнес-логика (создание заказов, работа с данными)

    public function __construct(FioDtrnService $fioDtrnService) {
        $this->fioDtrnService = $fioDtrnService;
    }

    public function index(Request $request) {

        $filterFio = $request->input('filter_fio');
        $filterKlId = $request->input('filter_kl_id');
        $filterSex = $request->input('filter_sex');
        $ripFilter = $request->input('rip', 0);

        $query = FioDtrn::query();

        if ($filterFio) { // <-- Используем $filterFio
            $query->where('fio', 'like', "%{$filterFio}%");
        }

        if ($filterKlId) { // <-- Используем $filterKlId
            $query->where('kl_id', 'like', "%{$filterKlId}%");
        }

        if ($filterSex) { // <-- Используем $filterSex
            $query->where('sex', $filterSex);
        }

        if ($ripFilter == 1) {
            $query->whereNotNull('rip_at');
        }

        // --- Фильтр по дате поездок ---
        $DateFrom = $request->input('date_from');
        $DateTo = $request->input('date_to');

        // Коллекция для хранения ID клиентов, у которых есть заказы в диапазоне
        $filteredClientIds = null;
        if ($DateFrom || $DateTo) {
            $orderQuery = Order::select('client_id'); // Ищем по client_id
            if ($DateFrom) {
                $orderQuery->whereDate('visit_data', '>=', $DateFrom);
            }
            if ($DateTo) {
                $orderQuery->whereDate('visit_data', '<=', $DateTo);
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
        if ($DateFrom) {
            $ordersCountQuery->whereDate('visit_data', '>=', $DateFrom);
        }
        if ($DateTo) {
            $ordersCountQuery->whereDate('visit_data', '<=', $DateTo);
        }

        if ($filteredClientIds !== null) {
            // Если фильтр по дате был, и $filteredClientIds содержит ID клиентов с заказами в диапазоне
            // Мы уже отфильтровали $query по этим ID
            // Теперь подсчитываем заказы только в этом диапазоне для каждого клиента в результирующем наборе
            $query->withCount(['orders' => function ($q) use ($DateFrom, $DateTo) {
                    if ($DateFrom) {
                        $q->whereDate('visit_data', '>=', $DateFrom);
                    }
                    if ($DateTo) {
                        $q->whereDate('visit_data', '<=', $DateTo);
                    }
                }]);
        } else {
            // Если фильтр по дате не применялся, подсчитываем все заказы
            $query->withCount('orders');
        }

        // Сортировка
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');

        $allowedSorts = ['id', 'fio', 'kl_id', 'data_r', 'sex', 'orders_count', 'rip_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id';
        }
        if (!in_array(strtolower($direction), ['asc', 'desc'])) {
            $direction = 'asc';
        }


        $fiodtrns = $query->orderBy($sort, $direction)->paginate(50);
        // --- ДИАГНОСТИКА ---
//        \Log::info('FioDtrn Index Paginated Results Count (items):', [$fiodtrns->count()]);
//        \Log::info('FioDtrn Index Paginated Results Items:', $fiodtrns->items());
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
        $urlParams = $this->fioDtrnService->getUrlParams();

        return view('fiodtrns.index', compact('fiodtrns', 'sort', 'direction',
                        'fiodtrnsJs', 'duplicateCounts', 'urlParams', 'ripFilter',
                        'filterFio', 'filterKlId', 'filterSex'));
    }

    public function create(Request $request) {
        $fiodtrn = new FioDtrn();
        // Получаем текущие параметры сортировки и фильтрации
        $urlParams = $this->fioDtrnService->getUrlParams();

        return view('fiodtrns.create', compact('fiodtrn', 'urlParams'));
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
        $urlParams = $this->fioDtrnService->getUrlParams();

        return redirect()->route('fiodtrns.index', $urlParams)->with('success', 'Клиент успешно создан');
    }

    public function show(Request $request, FioDtrn $fiodtrn) {

        $urlParams = $this->fioDtrnService->getUrlParams();
        return view('fiodtrns.show', compact('fiodtrn', 'urlParams'));
    }

    public function edit(Request $request, FioDtrn $fiodtrn) {
        // Получаем всех пользователей с ролью admin или operator
        $users = \App\Models\User::whereHas('roles', function ($query) {
                    $query->whereIn('name', ['admin', 'operator']);
                })->get();

        // Получаем текущие параметры сортировки и фильтрации
        $urlParams = $this->fioDtrnService->getUrlParams();

        return view('fiodtrns.edit', compact('fiodtrn', 'users', 'urlParams'));
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
            'kl_id' => 'required|string|max:191|unique:fio_dtrns,kl_id,' . $fiodtrn->id,
            'fio' => 'required|string|max:191',
            'client_invalid' => 'nullable|string|max:191',
            'data_r' => 'nullable|date',
            'sex' => 'nullable|in:М,Ж',
            'rip_at' => 'nullable|date|after_or_equal:data_r', // Если указана дата рождения
            'created_rip' => 'nullable|required_with:rip_at|date_format:Y-m-d\TH:i|after_or_equal:rip_at',
            'user_rip' => 'nullable|required_with:rip_at|exists:users,id',
            'komment' => 'nullable|string|max:1000',
        ]);

        $fiodtrn->update($request->all());

        // Передаем все параметры сортировки и фильтрации
        $urlParams = $this->fioDtrnService->getUrlParams();

        return redirect()->route('fiodtrns.index', $urlParams) // <-- Передаем $urlParams как есть
                        ->with('success', "Клиент {$fiodtrn->fio} обновлён");
    }

    public function destroy(Request $request, FioDtrn $fiodtrn) {
        
        // Передаем все параметры сортировки и фильтрации
        $urlParams = $this->fioDtrnService->getUrlParams();
        
         // Проверяем, есть ли у клиента заказы
        $hasOrders = $fiodtrn->orders()->exists();
    
        if ($hasOrders) {
            // Передаем все параметры сортировки и фильтрации
            $urlParams = $this->fioDtrnService->getUrlParams();
      
            return redirect()->route('fiodtrns.index', $urlParams)
                         ->with('error', "Невозможно удалить клиента {$fiodtrn->fio}, так как у него есть заказы");
        }
        
        $fiodtrn->delete();

        
        return redirect()->route('fiodtrns.index', $urlParams)->with('success', 'Клиент удалён');
    }

    public function showOrders(FioDtrn $fiodtrn, Request $request) {
        
        // Получаем фильтры даты из запроса
        $DateFrom = $request->input('date_from');
        $DateTo = $request->input('date_to');
      
        $orderQuery = Order::where('client_id', $fiodtrn->id)
            ->whereNull('deleted_at');

        // Применяем фильтры даты, если они указаны
        if ($DateFrom) {
            $orderQuery->whereDate('visit_data', '>=', $DateFrom);
        }
        if ($DateTo) {
            $orderQuery->whereDate('visit_data', '<=', $DateTo);
        }

        // Сортируем и пагинируем
        $orders = $orderQuery->orderBy('visit_data', 'desc')
            ->paginate(15);

        // Для корректной работы пагинации с параметрами, добавляем их к пагинатору
        $orders->appends($request->only(['date_from', 'date_to']));
        $urlParams = $this->fioDtrnService->getUrlParams();

        return view('fiodtrns.orders', compact('fiodtrn', 'orders', 'urlParams'));
    }

}
