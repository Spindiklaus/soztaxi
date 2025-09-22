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
use Carbon\Carbon;

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

            // Передаем параметры для кнопки "Назад"
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

            // Игнорируем значения из hidden полей и берем их из оригинального заказа на всякий случай
            $validated['user_id'] = $social_taxi_order->user_id; // Оригинальный оператор
            $validated['pz_nom'] = $social_taxi_order->pz_nom;   // Оригинальный номер
            $validated['pz_data'] = $social_taxi_order->pz_data;  // Оригинальная дата
            $validated['type_order'] = $social_taxi_order->type_order; // Оригинальный тип            
            $validated['client_id'] = $social_taxi_order->client_id;
            $validated['categoty_id'] = $social_taxi_order->category_id;
            $validated['kol_limit_all'] = $social_taxi_order->kol_limit_all;
            $validated['skidka_dop_all'] = $social_taxi_order->skidka_dop_all;

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

        $urlParams = request()->only(['sort', 'direction', 'show_deleted', 'pz_nom', 'type_order', 'status_order_id', 'date_from', 'date_to', 'user_id', 'client_fio']);
        return redirect()->route('social-taxi-orders.index', $urlParams)->with('success', 'Заказ успешно удален.');
    }

    public function restore($id) {
        // Загружаем заказ с учетом удаленных записей
        $order = Order::withTrashed()->find($id);

        if (!$order) {
            return redirect()->back()->with('error', 'Заказ не найден.');
        }

        $urlParams = request()->only(['sort', 'direction', 'show_deleted', 'pz_nom', 'type_order', 'status_order_id', 'date_from', 'date_to', 'user_id', 'client_fio']);

        if ($order->trashed()) {
            $order->restore();
            return redirect()->route('social-taxi-orders.index', $urlParams)->with('success', 'Заказ успешно восстановлен.');
        }

        return redirect()->route('social-taxi-orders.index', $urlParams)->with('error', 'Заказ не был удален.');
    }

    // Показать форму создания заказа по типу с поддержкой копирования
    public function createByType(int $type, Request $request) {
        // Проверяем допустимый тип
        if (!in_array($type, self::ALLOWED_TYPES)) {
            return redirect()->route('social-taxi-orders.index')->with('error', 'Недопустимый тип заказа.');
        }

        // Вызываем новый сервисный метод для получения данных
        $data = $this->orderService->getOrderCreateData($type);

        // Проверяем, есть ли параметр copy_from (копирование заказа)
        if ($request->has('copy_from')) {
            $copyFromId = $request->get('copy_from');
            $copiedOrderData = $this->orderService->getOrderDataForCopy($copyFromId, $type);
            if ($copiedOrderData) {
                // Добавляем автоматический комментарий о копировании
                $copiedOrder = $copiedOrderData['copiedOrder'] ?? null;
                if ($copiedOrder) {
                    $autoComment = "Копирование заказа №{$copiedOrder->pz_nom}, " .
                            "дата " . now()->format('d.m.Y H:i');

//                    // Объединяем с существующим комментарием, если он есть
//                    if (!empty($copiedOrder->komment)) {
//                        $autoComment = $copiedOrder->komment . "\n" . $autoComment;
//                    }
                    // Добавляем автоматический комментарий в данные
                    $copiedOrderData['autoComment'] = $autoComment;
                    // Добавляем флаг копирования для изменения заголовка
                    $copiedOrderData['isCopying'] = true;
                    $copiedOrderData['originalOrderNumber'] = $copiedOrder->pz_nom;
                }

                // Объединяем данные
                $data = array_merge($data, $copiedOrderData);
            }
        }

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

    // Показать форму отмены заказа
    public function showCancelForm(Order $social_taxi_order) {
        // Проверяем, что заказ не удален
        if ($social_taxi_order->deleted_at) {
            return redirect()->route('social-taxi-orders.show', $social_taxi_order)
                            ->with('error', 'Невозможно отменить удаленный заказ.');
        }

        // Проверяем, что заказ еще не отменен
        if ($social_taxi_order->cancelled_at) {
            return redirect()->route('social-taxi-orders.show', $social_taxi_order)
                            ->with('error', 'Заказ уже отменен.');
        }

        // Проверяем, что у заказа есть статус "Принят" (ID = 1)
        $currentStatus = $social_taxi_order->currentStatus;
        $statusId = $currentStatus ? $currentStatus->status_order_id : 1;

        if ($statusId != 1) {
            return redirect()->route('social-taxi-orders.show', $social_taxi_order)
                            ->with('error', 'Отмена возможна только для заказов со статусом "Принят".');
        }

        // Получаем параметры запроса для сохранения фильтров
        $urlParams = request()->only(['sort', 'direction', 'show_deleted', 'pz_nom', 'type_order', 'status_order_id', 'date_from', 'date_to', 'user_id', 'client_fio']);

        return view('social-taxi-orders.cancel', compact('social_taxi_order', 'urlParams'));
    }

    // Отменить заказ
    public function cancel(Request $request, Order $social_taxi_order) {
        try {
            // Валидация данных
            $validated = $request->validate([
                'reason' => 'required|string|max:1000',
                'cancelled_at' => [
                    'required',
                    'date',
                    'before_or_equal:now', // Дата отмены не может быть в будущем
                    function ($attribute, $value, $fail) use ($social_taxi_order) {
                        // Проверяем, что дата отмены раньше даты поездки
                        if ($social_taxi_order->visit_data) {
                            $cancelDate = Carbon::parse($value);
                            $visitDate = Carbon::parse($social_taxi_order->visit_data);

                            if ($cancelDate >= $visitDate) {
                                $fail('Дата отмены должна быть раньше даты поездки (' . $visitDate->format('d.m.Y H:i') . ').');
                            }
                        }
                    }
                ],
                'user_id' => 'required|integer|exists:users,id',
                    ], [
                'reason.required' => 'Причина отмены обязательна для заполнения.',
                'reason.string' => 'Причина отмены должна быть строкой.',
                'reason.max' => 'Причина отмены не может быть длиннее 1000 символов.',
                'cancelled_at.required' => 'Дата отмены обязательна для заполнения.',
                'cancelled_at.date' => 'Дата отмены должна быть корректной датой.',
                'cancelled_at.before_or_equal' => 'Дата отмены не может быть в будущем.',
                'user_id.required' => 'Оператор обязателен для выбора.',
                'user_id.integer' => 'ID оператора должен быть целым числом.',
                'user_id.exists' => 'Выбранный оператор не существует.',
            ]);

            // Проверяем, что заказ не удален
            if ($social_taxi_order->deleted_at) {
                return redirect()->route('social-taxi-orders.show', $social_taxi_order)
                                ->with('error', 'Невозможно отменить удаленный заказ.');
            }

            // Проверяем, что заказ еще не отменен
            if ($social_taxi_order->cancelled_at) {
                return redirect()->route('social-taxi-orders.show', $social_taxi_order)
                                ->with('error', 'Заказ уже отменен.');
            }

            // Проверяем, что у заказа есть статус "Принят" (ID = 1)
            $currentStatus = $social_taxi_order->currentStatus;
            $statusId = $currentStatus ? $currentStatus->status_order_id : 1;

            if ($statusId != 1) {
                return redirect()->route('social-taxi-orders.show', $social_taxi_order)
                                ->with('error', 'Отмена возможна только для заказов со статусом "Принят".');
            }

            \DB::beginTransaction();
            try {
                // Устанавливаем cancelled_at напрямую - это должно сработать в Observer
                $social_taxi_order->cancelled_at = $validated['cancelled_at'];
                $social_taxi_order->komment = ($social_taxi_order->komment ? $social_taxi_order->komment . "\n\n" : '') .
                        'Отмена заказа: ' . $validated['reason'] .
                        '. Оператор: ' . auth()->user()->name .
                        ' (' . auth()->user()->litera . ')' .
                        '. Дата отмены: ' . Carbon::parse($validated['cancelled_at'])->format('d.m.Y H:i');
                $social_taxi_order->updated_at = now();
                $social_taxi_order->save();

                \DB::commit();

                $urlParams = request()->only(['sort', 'direction', 'show_deleted', 'pz_nom', 'type_order', 'status_order_id', 'date_from', 'date_to', 'user_id', 'client_fio']);
                return redirect()->route('social-taxi-orders.show', array_merge(['social_taxi_order' => $social_taxi_order->id], $urlParams))
                                ->with('success', 'Заказ успешно отменен.');
            } catch (\Exception $e) {
                \DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            $urlParams = request()->only(['sort', 'direction', 'show_deleted', 'pz_nom', 'type_order', 'status_order_id', 'date_from', 'date_to', 'user_id', 'client_fio']);
            return redirect()->route('social-taxi-orders.index', $urlParams)
                            ->with('error', 'Ошибка при отмене заказа: ' . $e->getMessage());
        }
    }

}
