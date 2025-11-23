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

    public function __construct($orders, $dateFrom, $dateTo)
    {
        $this->orders = $orders;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function view(): View
    {
        return view('exports.social-taxi-orders', [
            'orders' => $this->orders,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'generatedAt' => now()
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        // Устанавливаем ширину колонок
        $sheet->getColumnDimension('A')->setWidth(8);   // № п/п
        $sheet->getColumnDimension('B')->setWidth(15);  // Тип поездки
        $sheet->getColumnDimension('C')->setWidth(15);  // № заказа
        $sheet->getColumnDimension('D')->setWidth(15);  // Дата поездки
        $sheet->getColumnDimension('E')->setWidth(30);  // Откуда
        $sheet->getColumnDimension('F')->setWidth(30);  // Куда
        $sheet->getColumnDimension('G')->setWidth(10);  // Обратно
        $sheet->getColumnDimension('H')->setWidth(15);  // Дата обратно
        $sheet->getColumnDimension('I')->setWidth(10);  // Скидка,%
        $sheet->getColumnDimension('J')->setWidth(10);  // Дальность
        $sheet->getColumnDimension('K')->setWidth(15);  // Цена за поездку
        $sheet->getColumnDimension('L')->setWidth(15);  // Сумма к оплате
        $sheet->getColumnDimension('M')->setWidth(15);  // Сумма к возмещению
        $sheet->getColumnDimension('N')->setWidth(25);  // ФИО
        $sheet->getColumnDimension('O')->setWidth(20);  // Категория инвалидности
        $sheet->getColumnDimension('P')->setWidth(20);  // Доп. сведения
        $sheet->getColumnDimension('Q')->setWidth(30);  // Комментарии

        // Подсветка сгруппированных заказов (кроме первого в группе)
        $startRow = 5; // Начинаем с 5-й строки (после заголовков)
        $currentGroup = null;

        foreach ($this->orders as $index => $order) {
            $rowNumber = $startRow + $index;

            if ($order->order_group_id && $order->order_group_id == $currentGroup) {
                // Это не первый заказ в группе - выделяем серым
                $sheet->getStyle("A{$rowNumber}:Q{$rowNumber}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'F2F2F2'] // Светло-серый фон
                    ]
                ]);
            } else {
                // Это первый заказ в группе или одиночный - оставляем белым
                $currentGroup = $order->order_group_id;
            }
        }

        return [];
    }

    public function columnFormats(): array
    {
        return [
            'C' => '@', // № заказа как текст
            'I' => '0.00', // Скидка,%
            'J' => '0.00', // Дальность
            'K' => '0.00', // Цена за поездку
            'L' => '0.00', // Сумма к оплате
            'M' => '0.00', // Сумма к возмещению
        ];
    }
}