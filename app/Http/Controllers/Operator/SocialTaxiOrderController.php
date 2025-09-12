<?php

namespace App\Http\Controllers\Operator;

use App\Queries\OrderQueryBuilder;
use App\Models\Order;
use App\Models\User;
use App\Models\FioDtrn;
use App\Models\Category;
use App\Models\SkidkaDop;
use App\Models\Taxi;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Requests\StoreSocialTaxiOrderRequest;
use App\Http\Requests\UpdateSocialTaxiOrderRequest;

class SocialTaxiOrderController extends BaseController {

    protected $queryBuilder;

    public function __construct(OrderQueryBuilder $queryBuilder) {
        $this->queryBuilder = $queryBuilder;
    }

    // Показать список заказов
    public function index(Request $request) {
        // По умолчанию показываем только неудаленные записи
        $showDeleted = $request->get('show_deleted', '0');

        $sort = $request->get('sort', 'pz_data');
        $direction = $request->get('direction', 'desc');

        // Получаем список операторов для фильтра
        $operators = User::orderBy('name')->get();

        // Собираем параметры для передачи в шаблон
        $urlParams = $request->only(['sort', 'direction', 'show_deleted', 'pz_nom', 'type_order', 'status_order_id', 'date_from', 'date_to']);

        $query = $this->queryBuilder->build($request, $showDeleted == '1');
        $orders = $query->paginate(15)->appends($request->all());

        return view('social-taxi-orders.index', compact(
                        'orders',
                        'showDeleted',
                        'sort',
                        'direction',
                        'urlParams', // Передаем параметры в шаблон
                        'operators'
        ));
    }

    // Показать форму создания заказа
    public function create() {
        return view('social-taxi-orders.create');
    }

    // Сохранить новый заказ
    public function store(StoreSocialTaxiOrderRequest $request) {
        Order::create($request->validated());

        return redirect()->route('social-taxi-orders.index')->with('success', 'Заказ успешно создан.');
    }

    // Показать конкретный заказ
    public function show($id) {

//        \Log::info('Попытка открыть заказ', ['order_id' => $id]);

        try {
            // Сначала попробуем найти заказ
            $order = Order::withTrashed()->find($id);

            if (!$order) {
                \Log::warning('Заказ не найден', ['order_id' => $id]);
                return redirect()->route('social-taxi-orders.index')
                                ->with('error', 'Заказ не найден.');
            }

//            \Log::info('Найден заказ', ['order_id' => $order->id, 'deleted_at' => $order->deleted_at]);
            // Загружаем все необходимые отношения
            $order->load([
                'client',
                'category',
                'dopus',
                'statusHistory.statusOrder',
                'statusHistory.user', // Загружаем пользователя для истории статусов
                'user',
                'taxi' // Загружаем оператора такси
            ]);

            // Получаем количество поездок клиента в месяце поездки
            $tripCount = getClientTripsCountInMonthByVisitDate($order->client_id, $order->visit_data);

            // Собираем параметры для кнопки "Назад"
            $backUrlParams = request()->only(['sort', 'direction', 'show_deleted', 'pz_nom', 'type_order', 'status_order_id', 'date_from', 'date_to']);

            return view('social-taxi-orders.show', compact(
                            'order',
                            'tripCount',
                            'backUrlParams' // Передаем параметры для кнопки "Назад"
            ));
        } catch (\Exception $e) {
            return redirect()->route('social-taxi-orders.index')
                            ->with('error', 'ЗПроизошла ошибка при открытии заказа.');
        }
    }

    // Показать форму редактирования заказа
    public function edit(Order $order) {
        return view('social-taxi-orders.edit', compact('order'));
    }

    // Обновить заказ
    public function update(UpdateSocialTaxiOrderRequest $request, Order $order) {
        $order->update($request->validated());

        return redirect()->route('social-taxi-orders.index')->with('success', 'Заказ успешно обновлен.');
    }

    // Удалить заказ (мягкое удаление)
    public function destroy($id) {
        // Загружаем заказ с учетом удаленных записей
        $order = Order::withTrashed()->find($id);

        if (!$order) {
            return redirect()->back()->with('error', 'Заказ не найден.');
        }

        // Проверяем текущий статус заказа
        $currentStatus = $order->currentStatus;
        $statusId = $currentStatus ? $currentStatus->status_order_id : 1; // По умолчанию "Принят"
        // Разрешаем удаление только для заказов со статусом "Принят" (ID = 1)
        if ($statusId != 1) {
            return redirect()->back()->with('error', 'Удаление возможно только для заказов со статусом "Принят". Текущий статус: ' . ($currentStatus->statusOrder->name ?? 'Неизвестный статус'));
        }

        // Принудительно устанавливаем deleted_at
        $order->deleted_at = now();
        $order->save();

        return redirect()->back()->with('success', 'Заказ успешно удален.');
    }

    public function restore($id) {
        // Загружаем заказ с учетом удаленных записей
        $order = Order::withTrashed()->find($id);

        if (!$order) {
            return redirect()->back()->with('error', 'Заказ не найден.');
        }

        if ($order->trashed()) {
            $order->restore();
            return redirect()->back()->with('success', 'Заказ успешно восстановлен.');
        }

        return redirect()->back()->with('error', 'Заказ не был удален.');
    }

    // Показать форму создания заказа по типу
    public function createByType($type) {
        // Проверяем допустимый тип
        $allowedTypes = [1, 2, 3]; // 1 - Соцтакси, 2 - Легковое авто, 3 - ГАЗель
        if (!in_array($type, $allowedTypes)) {
            return redirect()->route('social-taxi-orders.index')
                            ->with('error', 'Недопустимый тип заказа.');
        }


        $categories = Category::where(function ($query) use ($type) {
                    switch ($type) {
                        case 1: // Соцтакси
                            $query->where('is_soz', 1);
                            break;
                        case 2: // Легковое авто
                            $query->where('is_auto', 1);
                            break;
                        case 3: // ГАЗель
                            $query->where('is_gaz', 1);
                            break;
                    }
                })
                ->orderBy('nmv')
                ->get();

        // Получаем список операторов такси для выпадающего списка
        $taxis = Taxi::where('life', 1) // Только действующие операторы такси
                ->orderBy('name')
                ->get();
        // Если есть только один действующий оператор такси, устанавливаем его по умолчанию
    $defaultTaxiId = null;
    if ($taxis->count() == 1) {
        $defaultTaxiId = $taxis->first()->id;
    }

        // Получаем ID разрешенных категорий
        $allowedCategoryIds = $categories->pluck('id')->toArray();

        // Получаем список клиентов, которые имели заказы в разрешенных категориях
        $clients = FioDtrn::whereNull('rip_at') // Только живые клиенты
                ->whereHas('orders', function ($query) use ($allowedCategoryIds) {
                    $query->whereIn('category_id', $allowedCategoryIds)
                    ->whereNull('deleted_at')
                    ->whereNull('cancelled_at');
                })
                ->orderBy('fio')
                ->get();

        // Генерируем номер заказа заранее
        $orderNumber = generateOrderNumber($type, auth()->id());
        $orderDateTime = now();

        // Получаем список дополнительных условий для скидок
        $dopusConditions = SkidkaDop::where('life', 1) // Только действующие условия
                ->orderBy('name')
                ->get();

        return view('social-taxi-orders.create-by-type', compact(
                        'type',
                        'clients',
                        'categories',
                        'taxis',
                        'defaultTaxiId',
                        'orderNumber',
                        'orderDateTime',
                        'dopusConditions' // дополнительные условия
        ));
    }

    // Сохранить новый заказ по типу
    public function storeByType(Request $request, $type) {
        // Проверяем допустимый тип
        $allowedTypes = [1, 2, 3]; // 1 - Соцтакси, 2 - Легковое авто, 3 - ГАЗель
        if (!in_array($type, $allowedTypes)) {
            return redirect()->route('social-taxi-orders.index')
                            ->with('error', 'Недопустимый тип заказа.');
        }
        
        // Получаем текущую дату и время
        $now = now();
        // Завтрашняя дата от приема заказа (минимальная дата поездки)
        $minVisitDate = $now->copy()->addDay()->startOfDay();
         // Максимальная дата поездки - через полгода
        $maxVisitDate = $now->copy()->addMonths(6)->endOfDay();

        // Валидация данных
        $validated = $request->validate([
            'client_id' => 'required|exists:fio_dtrns,id',
            'visit_data' => [
            'required',
            'date',
            'after:' . $minVisitDate->format('Y-m-d H:i:s'), // Дата поездки должна быть после завтрашней даты
            'before:' . $maxVisitDate->format('Y-m-d H:i:s') // Дата поездки должна быть не позже чем через полгода                
            ],
            'adres_otkuda' => 'required|string|max:255',
            'adres_kuda' => 'required|string|max:255',
            'adres_obratno' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'client_tel' => 'required|string|max:255',
            'client_invalid' => 'nullable|string|max:255',
            'client_sopr' => 'nullable|string|max:255',
            'pz_nom' => 'required|string|max:255', // Номер заказа из формы
            'pz_data' => 'required|date', // Дата заказа из формы
            'type_order' => 'required|integer|in:1,2,3', // Тип заказа из формы
            'user_id' => 'required|integer|exists:users,id', // ID оператора из формы
            'taxi_id' => 'required|exists:taxis,id',
            // Дополнительные поля (могут отсутствовать)            
            'taxi_price' => 'nullable|numeric',
            'taxi_way' => 'nullable|numeric',
            'taxi_sent_at' => 'nullable|date',
            'otmena_data' => 'nullable|date',
            'otmena_taxi' => 'nullable|integer',
            'closed_at' => 'nullable|date',
            'komment' => 'nullable|string',
            'visit_obratno' => 'nullable|date',
            'predv_way' => 'nullable|numeric|min:0|max:100',
            'zena_type' => 'nullable|integer',
            'dopus_id' => 'nullable|exists:skidka_dops,id',
            'skidka_dop_all' => 'nullable|integer|in:50,100',
            'kol_p_limit' => 'nullable|integer|in:10,26',
            'category_skidka' => 'nullable|integer|in:50,100',
            'category_limit' => 'nullable|integer|min:10|max:10',
                ], [
            'client_id.required' => 'Клиент обязателен для выбора.',
            'client_id.exists' => 'Выбранный клиент не существует.',
            'visit_data.required' => 'Дата поездки обязательна для заполнения.',
            'visit_data.date' => 'Дата поездки должна быть корректной датой.',
            'visit_data.after' => 'Дата поездки должна быть не раньше завтрашней даты (' . $minVisitDate->format('d.m.Y') . ').',
            'visit_data.before' => 'Дата поездки должна быть не позже чем через полгода (' . $maxVisitDate->format('d.m.Y') . ').',
            'adres_otkuda.required' => 'Адрес отправки обязателен для заполнения.',
            'adres_otkuda.string' => 'Адрес отправки должен быть строкой.',
            'adres_otkuda.max' => 'Адрес отправки не может быть длиннее 255 символов.',
            'adres_kuda.required' => 'Адрес назначения обязателен для заполнения.',
            'adres_kuda.string' => 'Адрес назначения должен быть строкой.',
            'adres_kuda.max' => 'Адрес назначения не может быть длиннее 255 символов.',
            'adres_obratno.string' => 'Обратный адрес должен быть строкой.',
            'adres_obratno.max' => 'Обратный адрес не может быть длиннее 255 символов.',
            'category_id.required' => 'Категория обязательна для выбора.',
            'category_id.exists' => 'Выбранная категория не существует.',
            'client_tel.required' => 'Телефон для связи обязателен.',
            'client_tel.string' => 'Телефон клиента должен быть строкой.',
            'client_tel.max' => 'Телефон клиента не может быть длиннее 255 символов.',
            'client_invalid.string' => 'Удостоверение инвалида должно быть строкой.',
            'client_invalid.max' => 'Удостоверение инвалида не может быть длиннее 255 символов.',
            'client_sopr.string' => 'Сопровождающий должен быть строкой.',
            'client_sopr.max' => 'Сопровождающий не может быть длиннее 255 символов.',
            'pz_nom.required' => 'Номер заказа обязателен для заполнения.',
            'pz_nom.string' => 'Номер заказа должен быть строкой.',
            'pz_nom.max' => 'Номер заказа не может быть длиннее 255 символов.',
            'pz_data.required' => 'Дата заказа обязательна для заполнения.',
            'pz_data.date' => 'Дата заказа должна быть корректной датой.',
            'type_order.required' => 'Тип заказа обязателен для выбора.',
            'type_order.integer' => 'Тип заказа должен быть целым числом.',
            'type_order.in' => 'Недопустимый тип заказа.',
            'user_id.required' => 'Оператор обязателен для выбора.',
            'user_id.integer' => 'ID оператора должен быть целым числом.',
            'user_id.exists' => 'Выбранный оператор не существует.',
            'taxi_id.required' => 'Выбор оператора такси обязателен для сохранения заказа.',        
            'taxi_id.exists' => 'Выбранный оператор такси не существует.',
            'taxi_price.numeric' => 'Цена такси должна быть числом.',
            'taxi_way.numeric' => 'Дальность такси должна быть числом.',
            'taxi_sent_at.date' => 'Дата отправки в такси должна быть корректной датой.',
            'otmena_data.date' => 'Дата отмены должна быть корректной датой.',
            'otmena_taxi.integer' => 'Отмена такси должна быть целым числом.',
            'closed_at.date' => 'Дата закрытия должна быть корректной датой.',
            'komment.string' => 'Комментарий должен быть строкой.',
            'visit_obratno.date' => 'Дата обратной поездки должна быть корректной датой.',
            'predv_way.numeric' => 'Предварительная дальность должна быть числом.',
            'predv_way.min' => 'Предварительная дальность поездки не может быть отрицательной.',
            'predv_way.max' => 'Предварительная дальность поездки не может быть больше 100км.',
            'zena_type.integer' => 'Тип цены должен быть целым числом.',
            'dopus_id.exists' => 'Выбранные дополнительные условия не существуют.',
            'skidka_dop_all.integer' => 'Скидка по дополнительным условиям должна быть целым числом.',
            'skidka_dop_all.in' => 'Скидка по поездке может быть только 50 или 100%.',
            'kol_p_limit.integer' => 'Лимит поездок должен быть целым числом.',
            'kol_p_limit.in' => 'Лимит поездок может быть только 10 или 26 поездок в месяц.',
            'category_skidka.integer' => 'Скидка по категории должна быть целым числом.',
            'category_skidka.in' => 'Скидка по категории может быть только 50 или 100%.',                    
            'category_limit.integer' => 'Лимит по категории должен быть целым числом.',
            'category_limit.in' => 'Лимит поездок по категории может быть только 10.',
        ]);

        DB::beginTransaction();
        try {
            // Используем номер заказа из формы
            $pzNom = $validated['pz_nom'];

            // Проверяем, существует ли уже заказ с таким номером
            if (Order::where('pz_nom', $pzNom)->exists()) {
                throw new \Exception("Заказ с номером {$pzNom} уже существует.");
            }



            // Подготавливаем данные для создания заказа
            $orderData = [
                'type_order' => (int) ($validated['type_order'] ?? 1),
                'client_id' => (int) ($validated['client_id'] ?? 0),
                'client_tel' => $validated['client_tel'] ?? null,
                'client_invalid' => $validated['client_invalid'] ?? null,
                'client_sopr' => $validated['client_sopr'] ?? null,
                'category_id' => (int) ($validated['category_id'] ?? 0),
                'category_skidka' => $validated['category_skidka'] ? (int) $validated['category_skidka'] : null,
                'category_limit' => $validated['category_limit'] ? (int) $validated['category_limit'] : null,
                'dopus_id' => !empty($validated['dopus_id']) ? (int) $validated['dopus_id'] : null,
                'skidka_dop_all' => $validated['skidka_dop_all'] ? (int) $validated['skidka_dop_all'] : null,
                'kol_p_limit' => $validated['kol_p_limit'] ? (int) $validated['kol_p_limit'] : null,
                'pz_nom' => $pzNom,
                'pz_data' => $validated['pz_data'] ?? now(),
                'adres_otkuda' => $validated['adres_otkuda'] ?? null,
                'adres_kuda' => $validated['adres_kuda'] ?? null,
                'adres_obratno' => $validated['adres_obratno'] ?? null,
                'zena_type' => (int) ($validated['zena_type'] ?? 1),
                'visit_data' => $validated['visit_data'] ?? null,
                'predv_way' => isset($validated['predv_way']) && $validated['predv_way'] !== '' && $validated['predv_way'] !== null ?
                (float) str_replace(',', '.', $validated['predv_way']) : null,
                'taxi_id' => !empty($validated['taxi_id']) ? (int) $validated['taxi_id'] : null,
                'taxi_sent_at' => $validated['taxi_sent_at'] ?? null,
                'taxi_price' => isset($validated['taxi_price']) && $validated['taxi_price'] !== '' && $validated['taxi_price'] !== null ?
                (float) str_replace(',', '.', $validated['taxi_price']) : null,
                'taxi_way' => isset($validated['taxi_way']) && $validated['taxi_way'] !== '' && $validated['taxi_way'] !== null ?
                (float) str_replace(',', '.', $validated['taxi_way']) : null,
                'cancelled_at' => $validated['otmena_data'] ?? null,
                'otmena_taxi' => (int) ($validated['otmena_taxi'] ?? 0),
                'closed_at' => $validated['closed_at'] ?? null,
                'komment' => $validated['komment'] ?? null,
                'user_id' => (int) ($validated['user_id'] ?? auth()->id() ?? 1),
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'visit_obratno' => $validated['visit_obratno'] ?? null,
            ];

            // Создаем заказ
            $order = Order::create($orderData);

            // Устанавливаем начальный статус "Принят"
            $this->setInitialStatus($order, 1); // ID статуса "Принят"

            DB::commit();

            return redirect()->route('social-taxi-orders.show', $order)
                            ->with('success', 'Заказ успешно создан.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Ошибка при создании заказа", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Ошибка при создании заказа: ' . $e->getMessage())
                            ->withInput();
        }
    }

    // Получить данные клиента по AJAX
    public function getClientData($clientId) {
        try {
            // Получаем клиента
            $client = FioDtrn::find($clientId);
            if (!$client) {
                return response()->json(['error' => 'Клиент не найден'], 404);
            }
            
            // Получаем тип заказа из запроса (если есть)
            $typeOrder = request()->get('type_order', 1); // По умолчанию соцтакси

            // Получаем последние данные из предыдущих заказов клиента
            $lastOrder = Order::where('client_id', $clientId)
                    ->where('type_order', $typeOrder) // Учитываем тип заказа
                    ->whereNotNull('visit_data')
                    ->whereNull('deleted_at')
                    ->whereNull('cancelled_at')
                    ->orderBy('visit_data', 'desc')
                    ->first();

            // Получаем категории клиента из предыдущих заказов
            $clientCategories = Order::where('client_id', $clientId)
                    ->where('type_order', $typeOrder) // Учитываем тип заказа
                    ->whereNotNull('category_id')
                    ->whereNull('deleted_at')
                    ->whereNull('cancelled_at')
                    ->distinct()
                    ->pluck('category_id')
                    ->toArray();

            return response()->json([
                        'client' => [
                            'id' => $client->id,
                            'fio' => $client->fio,
                            'kl_id' => $client->kl_id,
                            'rip_at' => $client->rip_at,
                        ],
                        'last_order_data' => $lastOrder ? [
                    'client_tel' => $lastOrder->client_tel,
                    'client_invalid' => $lastOrder->client_invalid,
                    'client_sopr' => $lastOrder->client_sopr,
                    'category_id' => $lastOrder->category_id,
                    'category_skidka' => $lastOrder->category_skidka,
                    'category_limit' => $lastOrder->category_limit,
                    'dopus_id' => $lastOrder->dopus_id,
                    'skidka_dop_all' => $lastOrder->skidka_dop_all,
                    'kol_p_limit' => $lastOrder->kol_p_limit,
                        ] : null,
                        'client_categories' => $clientCategories,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка получения данных клиента: ' . $e->getMessage()], 500);
        }
    }

    // Установка начального статуса заказа
    private function setInitialStatus(Order $order, $statusId) {
        DB::table('order_status_histories')->insert([
            'order_id' => $order->id,
            'status_order_id' => $statusId,
            'user_id' => auth()->id() ?? 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

}
