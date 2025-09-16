<?php

namespace App\Http\Controllers\Operator;

use App\Models\Order;
use App\Models\User;
use App\Models\FioDtrn;
use App\Models\Category;
use App\Models\SkidkaDop;
use App\Models\Taxi;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSocialTaxiOrderRequest;
use App\Http\Requests\UpdateSocialTaxiOrderRequest;
use App\Http\Requests\StoreSocialTaxiOrderByTypeRequest;
use App\Services\SocialTaxiOrderService;
use App\Services\SocialTaxiOrderBuilder;


class SocialTaxiOrderController extends BaseController {

    protected $queryBuilder; // 
    protected $orderService; // бизнес-логика (создание заказов, работа с данными)

    public function __construct(SocialTaxiOrderBuilder $queryBuilder, SocialTaxiOrderService $orderService) {
        $this->queryBuilder = $queryBuilder;
        $this->orderService = $orderService;
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
        dd(__METHOD__);
    }

    // Сохранить новый заказ
    public function store(StoreSocialTaxiOrderRequest $request) {
        dd(__METHOD__);
    }

    // Показать конкретный заказ
    public function show($id) {

//        \Log::info('Попытка открыть заказ', ['order_id' => $id]);

        try {
            // Сначала попробуем найти заказ
            $order = Order::withTrashed()->find($id);
            $taxi = Taxi::find($order->taxi_id);

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
                            'backUrlParams', // Передаем параметры для кнопки "Назад"
                            'taxi'
            ));
        } catch (\Exception $e) {
            return redirect()->route('social-taxi-orders.index')
                            ->with('error', 'ЗПроизошла ошибка при открытии заказа.');
        }
    }

    // Показать форму редактирования заказа
    public function edit(Order $order) {
        dd(__METHOD__);        
    }

    // Обновить заказ
    public function update(UpdateSocialTaxiOrderRequest $request, Order $order) { 
        dd(__METHOD__);
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
    public function storeByType(StoreSocialTaxiOrderByTypeRequest $request, $type) {
        // Проверяем допустимый тип
        $allowedTypes = [1, 2, 3]; // 1 - Соцтакси, 2 - Легковое авто, 3 - ГАЗель
        if (!in_array($type, $allowedTypes)) {
            return redirect()->route('social-taxi-orders.index')
                            ->with('error', 'Недопустимый тип заказа.');
        }

        try {
            $validated = $request->validated();
            $order = $this->orderService->createOrderByType($validated, $type);

            return redirect()->route('social-taxi-orders.show', $order)
                            ->with('success', 'Заказ успешно создан.');
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка при создании заказа: ' . $e->getMessage())
                        ->withInput();
        }
    }

    // Получить данные клиента по AJAX
    public function getClientData($clientId) {
        try {
            $typeOrder = request()->get('type_order', 1);
            $clientData = $this->orderService->getClientData($clientId, $typeOrder);

            return response()->json($clientData);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
