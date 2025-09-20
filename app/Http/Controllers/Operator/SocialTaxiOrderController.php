<?php

namespace App\Http\Controllers\Operator;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSocialTaxiOrderRequest;
use App\Http\Requests\UpdateSocialTaxiOrderRequest;
use App\Http\Requests\StoreSocialTaxiOrderByTypeRequest;
use App\Services\SocialTaxiOrderService;
use App\Services\SocialTaxiOrderBuilder;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class SocialTaxiOrderController extends BaseController {

    /**
     * Константа для разрешенных типов заказа.
     * 1 - Соцтакси, 2 - Легковое авто, 3 - ГАЗель
     */
    protected const ALLOWED_TYPES = [1, 2, 3];

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
            // Вызываем метод сервиса для получения всех необходимых данных
            $data = $this->orderService->getOrderDetails($id);

            // Собираем параметры для кнопки "Назад"
            $backUrlParams = request()->only(['sort', 'direction', 'show_deleted', 'pz_nom', 'type_order', 'status_order_id', 'date_from', 'date_to']);

            // Передаем данные в представление, используя распаковку массива
            return view('social-taxi-orders.show', array_merge($data, ['backUrlParams' => $backUrlParams]));
        } catch (ModelNotFoundException $e) {
            // Обработка случая, когда заказ не найден
            \Log::warning('Заказ не найден', ['order_id' => $id]);
            return redirect()->route('social-taxi-orders.index')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            // Помещаем ошибку в лог с уровнем 'error'.
            // Вторым аргументом можно передать массив контекстных данных.
            \Log::error('Ошибка при открытии заказа: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('social-taxi-orders.index')->with('error', 'Произошла ошибка при открытии заказа.');
        }
    }

    // Показать форму редактирования заказа
    public function edit(Order $social_taxi_order) {
        try {
            $data = $this->orderService->getOrderEditData($social_taxi_order->id);

            // Передаем параметры для кнопки "Назад", если они нужны
            $backUrlParams = request()->only(['sort', 'direction', 'show_deleted', 'pz_nom', 'type_order', 'status_order_id', 'date_from', 'date_to']);

            return view('social-taxi-orders.edit', array_merge($data, ['backUrlParams' => $backUrlParams]));
        } catch (ModelNotFoundException $e) {
            \Log::error('Заказ для редактирования не найден.', ['order_id' => $social_taxi_order->id]);
            return redirect()->route('social-taxi-orders.index')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Ошибка при подготовке формы редактирования заказа.', ['order_id' => $social_taxi_order->id, 'exception' => $e]);
            return redirect()->back()->with('error', 'Произошла ошибка при открытии формы редактирования.');
        }
    }

    // Обновить заказ
    public function update(UpdateSocialTaxiOrderRequest $request, Order $social_taxi_order) {
//        dd(__METHOD__);
        try {
            $validated = $request->validated();

            // Передаем в сервис валидированные данные и объект заказа для обновления
            $this->orderService->updateOrder($social_taxi_order, $validated);

            return redirect()->route('social-taxi-orders.show', $social_taxi_order)
                            ->with('success', 'Заказ успешно обновлен.');
        } catch (\Exception $e) {
            \Log::error('Ошибка при обновлении заказа.', ['order_id' => $social_taxi_order->id, 'exception' => $e]);
            return back()->with('error', 'Ошибка при обновлении заказа: ' . $e->getMessage())
                            ->withInput();
        }
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
    public function createByType(int $type) {
        // Проверяем допустимый тип
        if (!in_array($type, self::ALLOWED_TYPES)) {
            return redirect()->route('social-taxi-orders.index')->with('error', 'Недопустимый тип заказа.');
        }

        // Вызываем новый сервисный метод для получения данных
        $data = $this->orderService->getOrderCreateData($type);

        return view('social-taxi-orders.create-by-type', $data);
    }
    // Сохранить новый заказ по типу
    public function storeByType(StoreSocialTaxiOrderByTypeRequest $request, $type) {
        // Проверяем допустимый тип соцзаказа
        if (!in_array($type, self::ALLOWED_TYPES)) {
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
