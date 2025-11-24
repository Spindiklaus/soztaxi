<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SocialTaxiOrdersExport implements FromView, WithStyles, WithColumnFormatting
{
    protected $orders;
    protected $dateFrom;
    protected $dateTo;
    protected $filters; 

    public function __construct($orders, $dateFrom, $dateTo, $filters = [])
    {
        $this->orders = $orders;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->filters = $filters;
    }

    public function view(): View
    {
        return view('exports.social-taxi-orders', [
            'orders' => $this->orders,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'generatedAt' => now(),
            'filters' => $this->filters // <--- Передаём фильтры в шаблон
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        // Устанавливаем ширину колонок
        $sheet->getColumnDimension('A')->setWidth(6);   // № п/п
        $sheet->getColumnDimension('B')->setWidth(10);  // Тип поездки
        $sheet->getColumnDimension('C')->setWidth(15);  // № заказа
        $sheet->getColumnDimension('D')->setWidth(12);  // Дата поездки
        $sheet->getColumnDimension('E')->setWidth(30);  // Откуда
        $sheet->getColumnDimension('F')->setWidth(30);  // Куда
        $sheet->getColumnDimension('G')->setWidth(30);  // Обратно
        $sheet->getColumnDimension('H')->setWidth(12);  // Дата обратно
        $sheet->getColumnDimension('I')->setWidth(10);  // Скидка,%
        $sheet->getColumnDimension('J')->setWidth(10);  // Дальность
        $sheet->getColumnDimension('K')->setWidth(15);  // Цена за поездку
        $sheet->getColumnDimension('L')->setWidth(15);  // Сумма к оплате
        $sheet->getColumnDimension('M')->setWidth(15);  // Сумма к возмещению
        $sheet->getColumnDimension('N')->setWidth(25);  // ФИО
        $sheet->getColumnDimension('O')->setWidth(20);  // Категория инвалидности
        $sheet->getColumnDimension('P')->setWidth(20);  // Доп. сведения
        
        // Устанавливаем перенос текста для заголовков (строка 5)
        for ($col = 'A'; $col <= 'P'; $col++) {
            $sheet->getStyle($col . '5')->getAlignment()->setWrapText(true);
        }
        
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(15);
        
        // высота для строки заголовков
         $sheet->getRowDimension(5)->setRowHeight(30);
        
         return [];
        
    }

    public function columnFormats(): array
    {
        return [
            'C' => '@', // № заказа как текст
            'I' => '0.00', // Скидка,%
            'J' => '0.000', // Дальность
            'K' => '0.00000000000', // Цена за поездку
            'L' => '0.00000000000', // Сумма к оплате
            'M' => '0.00000000000', // Сумма к возмещению
        ];
    }
}