<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TaxiOrdersExport implements FromView
{
    protected $orders;
    protected $visitDateFrom;
    protected $visitDateTo;

    public function __construct($orders, $visitDateFrom, $visitDateTo)
    {
        $this->orders = $orders;
        $this->visitDateFrom = $visitDateFrom;
        $this->visitDateTo = $visitDateTo;
    }

    public function view(): View
    {
        return view('exports.taxi-orders', [
            'orders' => $this->orders,
            'visitDateFrom' => $this->visitDateFrom,
            'visitDateTo' => $this->visitDateTo,
            'generatedAt' => now()
        ]);
    }
}