<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TaxiOrdersExport implements FromView, WithStyles, WithColumnFormatting
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

    public function styles(Worksheet $sheet)
    {
        // Устанавливаем ширину колонок
        $sheet->getColumnDimension('A')->setWidth(8);   // № п/п
        $sheet->getColumnDimension('B')->setWidth(15);  // Тип поездки
        $sheet->getColumnDimension('C')->setWidth(20); // № заказа
        $sheet->getColumnDimension('D')->setWidth(15); // Дата поездки
        $sheet->getColumnDimension('E')->setWidth(25);  // Откуда
        $sheet->getColumnDimension('F')->setWidth(25); // Куда
        $sheet->getColumnDimension('G')->setWidth(15); // Обратно
        $sheet->getColumnDimension('H')->setWidth(15); // Дата обратно
        $sheet->getColumnDimension('I')->setWidth(15); // Сотовый
        $sheet->getColumnDimension('J')->setWidth(10);  // Скидка
        $sheet->getColumnDimension('K')->setWidth(15); // Предв. дальность
        $sheet->getColumnDimension('L')->setWidth(15); // Цена за поездку
        $sheet->getColumnDimension('M')->setWidth(15); // Сумма к оплате
        $sheet->getColumnDimension('N')->setWidth(15); // Сумма к возмещению
        $sheet->getColumnDimension('O')->setWidth(20); // Категория
        $sheet->getColumnDimension('P')->setWidth(20); // Доп. сведения

        return [];
    }

    public function columnFormats(): array
    {
        return [
            'I' => '@', // Телефон как текст
            'C' => '@', // № заказа как текст
//            'J' => '@', // Скидка как текст
//            'K' => '@', // Предв. дальность как текст
        ];
    }
}