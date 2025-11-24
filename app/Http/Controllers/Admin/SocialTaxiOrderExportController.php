<?php
namespace App\Http\Controllers\Admin;

use App\Exports\SocialTaxiOrdersExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\SocialTaxiOrderBuilder;
use Illuminate\Http\Request;


class SocialTaxiOrderExportController extends BaseController
{
    protected $queryBuilder;

    public function __construct(SocialTaxiOrderBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Экспорт заказов в Excel
     */
    public function export(Request $request)
    {
        // Получаем данные по фильтрам (аналогично index, но без пагинации)
        $showDeleted = $request->get('show_deleted', '0');
        $query = $this->queryBuilder->build($request, $showDeleted == '1');
        $orders = $query->get();

        // Подготовим даты для заголовка
        $dateFrom = $request->input('date_from') ? \Carbon\Carbon::parse($request->input('date_from'))->format('d.m.Y') : '___.___.___';
        $dateTo = $request->input('date_to') ? \Carbon\Carbon::parse($request->input('date_to'))->format('d.m.Y') : '___.___.___';
        
        // --- ГЕНЕРАЦИЯ СПИСКА ФИЛЬТРОВ ---
    $activeFilters = [];
    $filterLabels = [
        'filter_pz_nom' => 'номер заказа',
        'filter_type_order' => 'тип заказа',
        'status_order_id' => 'статус заказа',
        'filter_user_id' => 'оператор',
        'client_fio' => 'ФИО клиента',
        'show_deleted' => 'статус записей',
        'date_from' => 'дата от',
        'date_to' => 'дата до'
    ];

    $statusLabels = [
        '1' => 'Принят',
        '2' => 'Передан в такси',
        '3' => 'Отменён',
        '4' => 'Закрыт'
    ];

    $typeLabels = [
        '1' => 'Соцтакси',
        '2' => 'Легковое авто',
        '3' => 'ГАЗель'
    ];

    // Получаем всех операторов для отображения их имён
    $operators = \App\Models\User::orderBy('name')->get()->keyBy('id');
    $operatorNames = [];
    foreach($operators as $operator) {
        $operatorNames[$operator->id] = $operator->name . ($operator->litera ? ' (' . $operator->litera . ')' : '');
    }

    foreach ($request->all() as $key => $value) {
        if (in_array($key, ['filter_pz_nom', 'filter_type_order', 'status_order_id', 'filter_user_id', 'client_fio', 'show_deleted', 'date_from', 'date_to']) && !empty($value)) {
            if ($key === 'status_order_id' && isset($statusLabels[$value])) {
                $activeFilters[] = $filterLabels[$key] . ': ' . $statusLabels[$value];
            } elseif ($key === 'filter_type_order' && isset($typeLabels[$value])) {
                $activeFilters[] = $filterLabels[$key] . ': ' . $typeLabels[$value];
            } elseif ($key === 'filter_user_id' && isset($operatorNames[$value])) {
                $activeFilters[] = $filterLabels[$key] . ': ' . $operatorNames[$value];
            } elseif ($key === 'show_deleted') {
                $activeFilters[] = $filterLabels[$key] . ': ' . ($value == '1' ? 'Все (включая удаленные)' : 'Только активные');
            } elseif ($key !== 'sort' && $key !== 'direction' && $key !== 'page') {
                $activeFilters[] = $filterLabels[$key] . ': ' . $value;
            }
        }
    }
    // --- КОНЕЦ ГЕНЕРАЦИИ ФИЛЬТРОВ ---

        
        

        return Excel::download(
            new SocialTaxiOrdersExport($orders, $dateFrom, $dateTo, $activeFilters), 
            'Заказы_соцтакси_' . now()->format('Ymd_His') . '.xlsx'
        );
    }
}