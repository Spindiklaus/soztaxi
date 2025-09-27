<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;

/**
 * Построитель запросов для таблицы заказов социального такси
 * 
 * Этот класс инкапсулирует всю логику построения запросов к таблице orders,
 * включая фильтрацию, сортировку и работу с удаленными записями.
 * 
 * Используется для уменьшения "толщины" контроллера и соблюдения принципа 
 * единственной ответственности (SRP).
 * 
 */
class SocialTaxiOrderBuilder {

    /**
     * @var \Illuminate\Database\Eloquent\Builder Экземпляр построителя запросов
     */
    protected $query;

    /**
     * Конструктор класса
     * 
     * Инициализирует построитель запросов с базовыми отношениями,
     * которые нужны для отображения списка заказов.
     */
    public function __construct() {
        // Создаем базовый запрос с предзагрузкой необходимых отношений
        $this->query = Order::with([
                    'currentStatus.statusOrder', // Текущий статус заказа
                    'client', // Клиент (заказчик)
                    'category', // Категория инвалидности
                    'dopus'                       // Дополнительные условия
        ]);
    }

    /**
     * Применяет фильтры к запросу
     * 
     * Обрабатывает все входящие параметры фильтрации:
     * - поиск по номеру заказа
     * - фильтр по типу заказа
     * - фильтр по диапазону дат
     * - фильтр по оператору (user_id)
     * - фильтр по ФИО клиента (client_fio)
     * 
     * @param Request $request HTTP-запрос с параметрами фильтрации
     * @return self Возвращает себя для цепочного вызова
     */
    public function applyFilters(Request $request): self {
        \Log::info('Apply filters params', [
            'filter_pz_nom' => $request->input('filter_pz_nom'),
            'filter_type_order' => $request->input('filter_type_order'),
            'status_order_id' => $request->input('status_order_id'),
            'filter_user_id' => $request->input('filter_user_id'),
            'client_fio' => $request->input('client_fio'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'all_request' => $request->all()
        ]);

        // Фильтрация
        if ($request->filled('filter_pz_nom')) {
            $this->query->where('pz_nom', 'like', '%' . $request->input('filter_pz_nom') . '%');
        }

        if ($request->filled('filter_type_order')) {
            $this->query->where('type_order', $request->input('filter_type_order'));
        }

        // Фильтрация по статусу заказа
        if ($request->filled('status_order_id')) {
            $this->query->whereHas('currentStatus', function ($q) use ($request) {
                $q->where('status_order_id', $request->input('status_order_id'));
            });
        }

        // Фильтрация по диапазону дат ПРИЕМА заказа
        $dateFrom = $request->input('date_from', '2016-08-01');
        $dateTo = $request->input('date_to', date('Y-m-d'));
        if ($dateFrom) {
            $this->query->whereDate('pz_data', '>=', $dateFrom);
        }
        if ($dateTo) {
            $this->query->whereDate('pz_data', '<=', $dateTo);
        }

        // Фильтрация по оператору (user_id)
        if ($request->filled('filter_user_id')) {
            $this->query->where('user_id', $request->input('filter_user_id'));
        }


        // Фильтрация по ФИО клиента
        if ($request->filled('client_fio')) {
            $this->query->whereHas('client', function ($q) use ($request) {
                $q->where('fio', 'like', '%' . $request->input('client_fio') . '%');
            });
        }

        // Фильтрация по диапазону дат ПОЕЗДКИ
        $visitDateFrom = $request->input('visit_date_from');
        $visitDateTo = $request->input('visit_date_to');

        if ($visitDateFrom) {
            $this->query->whereDate('visit_data', '>=', $visitDateFrom);
        }
        if ($visitDateTo) {
            $this->query->whereDate('visit_data', '<=', $visitDateTo);
        }


        return $this;
    }

    /**
     * Применяет сортировку к запросу
     * 
     * Обрабатывает параметры сортировки и применяет соответствующий ORDER BY.
     * Поддерживает сортировку по:
     * - дате приема заказа (pz_data)
     * - дате поездки (visit_data)  
     * - ФИО клиента (client_fio)
     * - ID заказа (id)
     * 
     * @param Request $request HTTP-запрос с параметрами сортировки
     * @return self Возвращает себя для цепочного вызова
     */
    public function applySorting(Request $request): self {
        // Получаем параметры сортировки из запроса
        $sort = $request->input('sort', 'pz_data');        // Поле для сортировки
        $direction = $request->input('direction', 'desc'); // Направление (ASC/DESC)
        // Защита от некорректных значений сортировки
        $allowedSorts = ['pz_data', 'visit_data', 'client_fio', 'id'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'pz_data'; // По умолчанию сортируем по дате приема
        }

        // Защита от некорректного направления сортировки
        if (!in_array(strtolower($direction), ['asc', 'desc'])) {
            $direction = 'desc'; // По умолчанию DESC
        }

        // Применяем соответствующую сортировку в зависимости от поля
        switch ($sort) {
            case 'client_fio':
                // Специальная сортировка по ФИО клиента через JOIN
                $this->applyClientFioSorting($direction);
                break;
            case 'visit_data':
                // Сортировка по дате поездки
                $this->query->orderBy('visit_data', $direction);
                break;
            case 'pz_data':
            default:
                // Сортировка по дате приема заказа (по умолчанию)
                $this->query->orderBy('pz_data', $direction);
                break;
        }

        return $this;
    }

    /**
     * Применяет сортировку по ФИО клиента
     * 
     * Для сортировки по ФИО требуется JOIN с таблицей клиентов,
     * так как ФИО хранится в отдельной таблице.
     * 
     * @param string $direction Направление сортировки (ASC/DESC)
     * @return void
     */
    protected function applyClientFioSorting(string $direction): void {
        $this->query->join('fio_dtrns', 'orders.client_id', '=', 'fio_dtrns.id')
                ->select('orders.*', 'fio_dtrns.fio as client_fio_sort')
                ->orderBy('client_fio_sort', $direction);
    }

    /**
     * Включает/исключает удаленные записи в результат
     * 
     * При включении удаленных записей использует withTrashed(),
     * что позволяет показывать как активные, так и удаленные заказы.
     * 
     * @param bool $withTrashed Флаг включения удаленных записей
     * @return self Возвращает себя для цепочного вызова
     */
    public function withTrashed(bool $withTrashed = true): self {
        if ($withTrashed) {
            $this->query->withTrashed();
        }
        return $this;
    }

    /**
     * Выполняет пагинацию результатов
     * 
     * @param int $perPage Количество записей на страницу
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15) {
        return $this->query->paginate($perPage);
    }

    /**
     * Выполняет запрос и возвращает все результаты
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get() {
        return $this->query->get();
    }

    /**
     * Собирает итоговый запрос
     * 
     * Основной метод, который объединяет все фильтры, сортировку 
     * и настройки отображения удаленных записей.
     * 
     * @param Request $request HTTP-запрос
     * @param bool $withTrashed Флаг включения удаленных записей
     * @return self Возвращает себя для дальнейшего использования
     */
    public function build(Request $request, bool $withTrashed = false) {
        // Применяем настройку отображения удаленных записей
        if ($withTrashed) {
            $this->withTrashed();
        }

        // Последовательно применяем фильтры и сортировку
        return $this->applyFilters($request)
                        ->applySorting($request);
    }

}
