<?php

// app/Http/Controllers/Admin/OrderGroupController.php

namespace App\Http\Controllers\Admin;

use App\Models\OrderGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; // Импортируем Validator

class OrderGroupController extends BaseController // 
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = OrderGroup::query(); // Начинаем строить запрос
        // Фильтрация по дате поездки
        $dateFrom = $request->input('date_from', '2025-01-01');
        $dateTo = $request->input('date_to', date('Y-m-d', strtotime('+6 months')));
        if ($dateFrom) {
            $query->whereDate('visit_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('visit_date', '<=', $dateTo);
        }
        
        // Фильтрация по названию (частичное совпадение)
        $nameFilter = $request->input('filter_name');
        if ($nameFilter) {
            $query->where('name', 'like', '%' . $nameFilter . '%');
        }
        
        // Сортировка
        $sort = $request->input('sort', 'visit_date'); // Поле для сортировки (по умолчанию 'visit_date')
        $direction = $request->input('direction', 'desc'); // Направление (по умолчанию 'desc')

        // Проверяем, что поле сортировки допустимо, чтобы избежать ошибок безопасности
        $allowedSorts = ['visit_date', 'name', 'taxi_way', 'taxi_price', 'taxi_vozm', 'created_at', 'orders_count'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'visit_date'; // Значение по умолчанию, если передано недопустимое поле
        }
        
        // Проверяем направление
        if (!in_array(strtolower($direction), ['asc', 'desc'])) {
            $direction = 'desc'; // Значение по умолчанию, если передано недопустимое направление
        }

        // Применяем сортировку
        if ($sort === 'orders_count') {
            // Если сортировка по количеству заказов, используем withCount
            $query->withCount('orders')->orderBy('orders_count', $direction);
        } else {
            // Иначе сортировка по обычному полю
            $query->orderBy($sort, $direction);
        }
        
        // Добавляем withCount для подсчета заказов ---
        $orderGroups = $query->withCount('orders')->paginate(20)->appends($request->all());
        // appends($request->all()) сохраняет все параметры запроса (фильтры, сортировку) в ссылках пагинации

        // Собираем параметры для передачи в шаблон ---
        $urlParams = $request->only(['sort', 'direction', 'date_from', 'date_to', 'filter_name']);

        return view('order-groups.index', compact('orderGroups', 'urlParams')); // Передаем и группы, и параметры

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Для OrderGroup, возможно, не нужно отдельной формы создания,
        // так как группы создаются через логику группировки.
        // Но если нужно, реализуйте здесь.
        // return view('admin.order_groups.create');
        abort(404); // Пока возвращаем 404, если создание не предусмотрено вручную
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Аналогично, сохранение происходит через логику группировки.
        // abort(404); // Пока возвращаем 404, если сохранение не предусмотрено вручную
        // Или реализуйте логику, если нужно создавать группы вручную.
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'visit_data' => [
                'required',
                'date',
            ],            
            // Добавьте другие поля, если есть
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $orderGroup = OrderGroup::create($validator->validated());

        return redirect()->route('admin.order-groups.index')->with('success', 'Группа успешно создана.');
    }

    /**
     * Display the specified resource.
     */
    public function show(OrderGroup $orderGroup) // Model Binding: Laravel автоматически найдет OrderGroup по ID
    {
        // Загружаем связанные заказы для отображения
        $orderGroup->load('orders.client'); // Загружаем заказы и их клиентов
        
        $urlParams = request()->only(['sort', 'direction', 'date_from', 'date_to', 'filter_name']);
//        dd($urlParams);
        
        return view('order-groups.show', compact('orderGroup', 'urlParams'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrderGroup $orderGroup)
    {
        $urlParams = request()->only(['sort', 'direction', 'date_from', 'date_to', 'filter_name']);

        return view('order-groups.edit', compact('orderGroup', 'urlParams'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrderGroup $orderGroup)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'visit_date' => [
                'required',
                'date',
            ],
            'komment' => 'nullable|string|max:1000', // Комментарий
            // Добавьте другие поля, если есть
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $orderGroup->update($request->only(['name', 'visit_date', 'komment'])); 
        // Указываем только разрешённые поля
        // Получаем параметры из $request->all(), включая скрытые поля формы ---
        $allowedParams = ['sort', 'direction', 'date_from', 'date_to', 'filter_name']; // все разрешённые параметры
        $urlParams = $request->only($allowedParams);

        return redirect()->route('order-groups.index', $urlParams)->with('success', 'Группа успешно обновлена.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrderGroup $orderGroup)
    {
        // Важно: Удаление группы может повлиять на связанные заказы.
        // Перед удалением вызывается deleting(OrderGroup $orderGroup)
        // $orderGroup->orders()->update(['order_group_id' => null]); // Пример

        $orderGroup->delete();

        return redirect()->route('order-groups.index')->with('success', 'Группа успешно удалена.');
    }
}