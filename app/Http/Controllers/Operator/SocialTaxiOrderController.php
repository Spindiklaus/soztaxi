<?php

namespace App\Http\Controllers\Operator;

use App\Queries\OrderQueryBuilder;
use App\Models\Order;
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

        // Собираем параметры для передачи в шаблон
        $urlParams = $request->only(['sort', 'direction', 'show_deleted', 'pz_nom', 'type_order', 'status_order_id', 'date_from', 'date_to']);

        $query = $this->queryBuilder->build($request, $showDeleted == '1');
        $orders = $query->paginate(15)->appends($request->all());

        return view('social-taxi-orders.index', compact(
                        'orders',
                        'showDeleted',
                        'sort',
                        'direction',
                        'urlParams' // Передаем параметры в шаблон
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
    public function createByType($type) 
    {
        // Проверяем допустимый тип
        $allowedTypes = [1, 2, 3]; // 1 - Соцтакси, 2 - Легковое авто, 3 - ГАЗель
        if (!in_array($type, $allowedTypes)) {
            return redirect()->route('social-taxi-orders.index')
                            ->with('error', 'Недопустимый тип заказа.');
        }
        
        // Получаем список клиентов для выпадающего списка
        $clients = FioDtrn::whereNull('rip_at') // Только живые клиенты
                          ->orderBy('fio')
                          ->get();
        
        // Получаем список категорий для выпадающего списка
        $categories = Category::where('is_soz', 1) // Только действующие категории
                             ->orderBy('nmv')
                             ->get();
        
        return view('social-taxi-orders.create-by-type', compact('type', 'clients', 'categories'));
    }
    
    // Сохранить новый заказ по типу
    public function storeByType(Request $request, $type) 
    {
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
            // Генерируем номер заказа
            $pzNom = $this->generateOrderNumber($type);
            
            // Подготавливаем данные для создания заказа
            $orderData = [
                'type_order' => (int)$type,
                'client_id' => (int)$validated['client_id'],
                'client_tel' => $validated['client_tel'] ?? null,
                'client_invalid' => $validated['client_invalid'] ?? null,
                'client_sopr' => $validated['client_sopr'] ?? null,
                'category_id' => (int)$validated['category_id'],
                'adres_otkuda' => $validated['adres_otkuda'],
                'adres_kuda' => $validated['adres_kuda'],
                'adres_obratno' => $validated['adres_obratno'] ?? null,
                'pz_nom' => $pzNom,
                'pz_data' => now(),
                'visit_data' => $validated['visit_data'],
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
    
    
    // Установка начального статуса заказа
    private function setInitialStatus(Order $order, $statusId) 
    {
        DB::table('order_status_histories')->insert([
            'order_id' => $order->id,
            'status_order_id' => $statusId,
            'user_id' => auth()->id() ?? 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

}
