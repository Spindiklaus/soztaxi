<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\OrderReportBuilder;
use App\Exports\OrderReportExport;
use Maatwebsite\Excel\Facades\Excel;


class OrderReportController extends BaseController
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        $query = new OrderReportBuilder($startDate, $endDate);
        $report = $query->build();

        return view('reports.orders_visit', compact('report', 'startDate', 'endDate'));
    }
    public function export(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        return Excel::download(new OrderReportExport($startDate, $endDate), 'сводка_по_статусам_'.now()->format('Y-m-d_H-i-s').'.xlsx');
    }
    
}