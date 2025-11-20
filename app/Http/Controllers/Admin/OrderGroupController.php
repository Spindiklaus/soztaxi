<?php

// app/Http/Controllers/Admin/OrderGroupController.php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\OrderGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; // Импортируем Validator
use Carbon\Carbon;

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
        
        // --- Проверка даты поездки и минимальной даты заказа. Они должны совпадать ---
        $newVisitDate = Carbon::parse($request->input('visit_date'));

        // Загружаем связанные заказы для проверки
        $relatedOrders = $orderGroup->orders; // Используем отношение 'orders'

        if ($relatedOrders->isNotEmpty()) {
            // Находим самый ранний заказ в группе
            $earliestOrder = $relatedOrders->sortBy('visit_data')->first();
            $expectedDateTime = $earliestOrder->visit_data; // Carbon объект с датой и временем

            // Сравниваем новую дату/время группы с датой/временем самого раннего заказа, но только до минуты
            // Для этого уберём секунды и микросекунды из обеих дат и сравним их.
            $newVisitDateWithoutSeconds = $newVisitDate->copy()->second(0)->microsecond(0);
            $expectedDateTimeWithoutSeconds = $expectedDateTime->copy()->second(0)->microsecond(0);

            if ($newVisitDateWithoutSeconds->ne($expectedDateTimeWithoutSeconds)) { // ne = not equal
                return redirect()->back()
                    ->withErrors(['visit_date' => "Дата и время группы должны совпадать с датой и временем самого раннего заказа в группе ({$expectedDateTime->format('d.m.Y H:i')})."])
                    ->withInput();
            }
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
    
    /**
     * Добавить заказ в группу
     */
    public function addOrderToGroup(Request $request, OrderGroup $orderGroup)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id', // Проверяем, что ID заказа передан и существует
        ]);

        $orderId = $request->input('order_id');

        try {
            // Загружаем заказ, который хотим добавить
            $orderToAdd = Order::findOrFail($orderId);

            // --- Валидация ---
            // 1. Заказ не в другой группе
            if ($orderToAdd->order_group_id !== null) {
                return response()->json(['success' => false, 'message' => 'Заказ уже входит в другую группу.'], 422);
            }

            // 2. Дата поездки совпадает с датой группы
            if ($orderToAdd->visit_data->format('Y-m-d') !== $orderGroup->visit_date->format('Y-m-d')) {
                return response()->json(['success' => false, 'message' => 'Дата поездки заказа не совпадает с датой поездки группы.'], 422);
            }

            // 3. Время поездки в пределах 45 минут от времени группы (берем время начала группы как visit_date)
            $groupStartTime = $orderGroup->visit_date; // Это datetime
            $orderVisitTime = $orderToAdd->visit_data; // Это datetime

            $diffInMinutes = $groupStartTime->diffInMinutes($orderVisitTime, false); // false означает, что может быть отрицательное значение

            if (abs($diffInMinutes) > 45) {
                return response()->json(['success' => false, 'message' => 'Время поездки заказа отличается от времени группы более чем на 45 минут.'], 422);
            }

            // 4. Заказ не удалён
            if ($orderToAdd->deleted_at !== null) {
                return response()->json(['success' => false, 'message' => 'Заказ удалён.'], 422);
            }

            // 5. Заказ не отменён
            if ($orderToAdd->cancelled_at !== null) {
                return response()->json(['success' => false, 'message' => 'Заказ отменён.'], 422);
            }

            // 6. Статус заказа "Принят" (не передан в такси)
            if ($orderToAdd->taxi_sent_at !== null) {
                return response()->json(['success' => false, 'message' => 'Заказ уже передан в такси.'], 422);
            }

            // 7. В группе не больше 3 заказов
            $currentOrderCount = $orderGroup->orders()->count(); // Используем отношение
            if ($currentOrderCount >= 3) {
                return response()->json(['success' => false, 'message' => 'В группе уже 3 заказа. Невозможно добавить больше.'], 422);
            }
            // --- Конец валидации ---

            // Обновляем заказ, присвоив ему ID группы
            $orderToAdd->update(['order_group_id' => $orderGroup->id]);

            // Возвращаем успешный ответ
            return response()->json(['success' => true, 'message' => 'Заказ успешно добавлен в группу.', 'order' => $orderToAdd->load('client')]); // Возвращаем обновлённый заказ с клиентом

        } catch (\Exception $e) {
            \Log::error('Ошибка при добавлении заказа в группу: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Произошла ошибка при добавлении заказа.'], 500);
        }
    }
    
    /**
     * Получить доступные заказы для добавления в группу
     */
    public function getAvailableOrdersForGroup(OrderGroup $orderGroup)
    {
        try {
            $groupId = $orderGroup->id;
            $groupDate = $orderGroup->visit_date->format('Y-m-d');
            $groupTime = $orderGroup->visit_date; // Это DateTime

            // Запрос для получения заказов, подходящих по критериям
            $availableOrders = Order::where('visit_data', '>=', $groupTime->copy()->subMinutes(45)) // Время не раньше чем -45 мин от времени группы
                                  ->where('visit_data', '<=', $groupTime->copy()->addMinutes(45))  // Время не позже чем +45 мин от времени группы
                                  ->whereDate('visit_data', $groupDate) // Совпадение даты
                                  ->whereNull('order_group_id') // Не в группе
                                  ->whereNull('deleted_at') // Не удалён
                                  ->whereNull('cancelled_at') // Не отменён
                                  ->whereNull('taxi_sent_at') // Не передан в такси
                                  ->with(['client']) // Загружаем клиента
                                  ->orderBy('visit_data') // Сортируем по времени
                                  ->get();

            // Проверяем лимит на 3 заказа в группе перед отправкой
            $currentOrderCount = $orderGroup->orders()->count();
            if ($currentOrderCount >= 3) {
                // Если лимит достигнут, возвращаем пустой массив
                $availableOrders = collect(); // Пустая коллекция
            }

            return response()->json([
                'success' => true,
                'orders' => $availableOrders
            ]);

        } catch (\Exception $e) {
            \Log::error('Ошибка при получении доступных заказов для группы: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Произошла ошибка при загрузке заказов.'], 500);
        }
    }
    
    /**
     * Удалить заказ из группы
     */
    public function removeOrderFromGroup(OrderGroup $orderGroup, Order $order) // Model Binding для обеих моделей
    {
        try {
            // Проверяем, что заказ действительно принадлежит этой группе
            if ($order->order_group_id !== $orderGroup->id) {
                return redirect()->back()->with('error', 'Заказ не принадлежит этой группе.');
            }

            // --- Проверка ограничений ---
            // 1. В группе должен остаться минимум один заказ
            $currentOrderCount = $orderGroup->orders()->count(); // Используем отношение
            if ($currentOrderCount <= 1) {
                return redirect()->back()->with('error', 'Невозможно удалить заказ: в группе должен остаться минимум один заказ.');
            }

            // 2. Заказ должен иметь статус "Принят" (предположим, это означает, что taxi_sent_at = null)
            if ($order->taxi_sent_at !== null) {
                return redirect()->back()->with('error', 'Невозможно удалить заказ: заказ уже передан в такси.');
            }
            // --- Конец проверки ---

            // Удаляем связь заказа с группой
            $order->update(['order_group_id' => null]); // Устанавливаем order_group_id в NULL

            return redirect()->back()->with('success', 'Заказ успешно удалён из группы.');

        } catch (\Exception $e) {
            \Log::error('Ошибка при удалении заказа из группы: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Произошла ошибка при удалении заказа.');
        }
    }

    
}