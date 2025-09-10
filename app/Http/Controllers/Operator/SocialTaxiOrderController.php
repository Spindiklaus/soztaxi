<?php

namespace App\Http\Controllers\Operator;

use App\Queries\OrderQueryBuilder;
use App\Models\Order;
use App\Models\User;
use App\Models\FioDtrn;
use App\Models\Category;
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

        return view('social-taxi-orders.create-by-type', compact('type', 'clients', 'categories', 'orderNumber', 'orderDateTime'));
    }

    // Сохранить новый заказ по типу
    public function storeByType(Request $request, $type) {
        // Проверяем допустимый тип
        $allowedTypes = [1, 2, 3]; // 1 - Соцтакси, 2 - Легковое авто, 3 - ГАЗель
        if (!in_array($type, $allowedTypes)) {
            return redirect()->route('social-taxi-orders.index')
                            ->with('error', 'Недопустимый тип заказа.');
        }

        // Валидация данных
        $validated = $request->validate([
            'client_id' => 'required|exists:fio_dtrns,id',
            'visit_data' => 'required|date',
            'adres_otkuda' => 'required|string|max:255',
            'adres_kuda' => 'required|string|max:255',
            'adres_obratno' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'client_tel' => 'nullable|string|max:255',
            'client_invalid' => 'nullable|string|max:255',
            'client_sopr' => 'nullable|string|max:255',
            'pz_nom' => 'required|string|max:255', // Номер заказа из формы
            'pz_data' => 'required|date', // Дата заказа из формы
            'type_order' => 'required|integer|in:1,2,3', // Тип заказа из формы
            'user_id' => 'required|integer|exists:users,id', // ID оператора из формы
                ], [
            'client_id.required' => 'Клиент обязателен для выбора.',
            'client_id.exists' => 'Выбранный клиент не существует.',
            'visit_data.required' => 'Дата поездки обязательна для заполнения.',
            'visit_data.date' => 'Дата поездки должна быть корректной датой.',
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
            'client_tel.string' => 'Телефон клиента должен быть строкой.',
            'client_tel.max' => 'Телефон клиента не может быть длиннее 255 символов.',
            'client_invalid.string' => 'Удостоверение инвалида должно быть строкой.',
            'client_invalid.max' => 'Удостоверение инвалида не может быть длиннее 255 символов.',
            'client_sopr.string' => 'Сопровождающий должен быть строкой.',
            'client_sopr.max' => 'Сопровождающий не может быть длиннее 255 символов.',
        ]);

        DB::beginTransaction();
        try {
            // Используем номер заказа из формы
            $pzNom = validated['pz_nom'];

            // Проверяем, существует ли уже заказ с таким номером
            if (Order::where('pz_nom', $pzNom)->exists()) {
                throw new \Exception("Заказ с номером {$pzNom} уже существует.");
            }



            // Подготавливаем данные для создания заказа
            $orderData = [
                'type_order' => (int) $validated['type_order'],
                'client_id' => (int) $validated['client_id'],
                'client_tel' => $validated['client_tel'] ?? null,
                'client_invalid' => $validated['client_invalid'] ?? null,
                'client_sopr' => $validated['client_sopr'] ?? null,
                'category_id' => (int) $validated['category_id'],
                'adres_otkuda' => $validated['adres_otkuda'],
                'adres_kuda' => $validated['adres_kuda'],
                'adres_obratno' => $validated['adres_obratno'] ?? null,
                'zena_type' => (int) ($validated['zena_type'] ?? 1),
                'pz_nom' => $pzNom,
                'pz_data' => $validated['pz_data'],
                'visit_data' => $validated['visit_data'] ?? null,
                'visit_obratno' => $validated['visit_obratno'] ?? null,
                'predv_way' => $validated['predv_way'] ? (float) str_replace(',', '.', $validated['predv_way']) : null,
                'taxi_id' => !empty($validated['taxi_id']) ? (int) $validated['taxi_id'] : null,
                'taxi_sent_at' => $validated['taxi_sent_at'] ?? null,
                'taxi_price' => $validated['taxi_price'] ? (float) str_replace(',', '.', $validated['taxi_price']) : null,
                'taxi_way' => $validated['taxi_way'] ? (float) str_replace(',', '.', $validated['taxi_way']) : null,
                'cancelled_at' => $validated['otmena_data'] ?? null,
                'otmena_taxi' => (int) ($validated['otmena_taxi'] ?? 0),
                'closed_at' => $validated['closed_at'] ?? null,
                'komment' => $validated['komment'] ?? null,
                'user_id' => auth()->id() ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
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

            // Получаем последние данные из предыдущих заказов клиента
            $lastOrder = Order::where('client_id', $clientId)
                    ->whereNotNull('visit_data')
                    ->whereNull('deleted_at')
                    ->whereNull('cancelled_at')
                    ->orderBy('visit_data', 'desc')
                    ->first();

            // Получаем категории клиента из предыдущих заказов
            $clientCategories = Order::where('client_id', $clientId)
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
