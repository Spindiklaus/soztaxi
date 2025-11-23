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

        return Excel::download(
            new SocialTaxiOrdersExport($orders, $dateFrom, $dateTo),
            'Заказы_соцтакси_' . now()->format('Ymd_His') . '.xlsx'
        );
    }
}