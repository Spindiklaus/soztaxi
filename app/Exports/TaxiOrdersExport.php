<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TaxiOrdersExport implements FromView, WithStyles, WithColumnFormatting
{
    protected $orders;
    protected $visitDateFrom;
    protected $visitDateTo;
    protected $taxi;

    public function __construct($orders, $visitDateFrom, $visitDateTo, $taxi)
    {
        $this->orders = $orders;
        $this->visitDateFrom = $visitDateFrom;
        $this->visitDateTo = $visitDateTo;
        $this->taxi = $taxi;
    }

    public function view(): View
    {
        return view('exports.taxi-orders', [
            'orders' => $this->orders,
            'visitDateFrom' => $this->visitDateFrom,
            'visitDateTo' => $this->visitDateTo,
            'generatedAt' => now(),
            'taxi' => $this->taxi
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        // Устанавливаем ширину колонок
        $sheet->getColumnDimension('A')->setWidth(8);   // № п/п
//        $sheet->getColumnDimension('B')->setWidth(15);  // Тип поездки
        $sheet->getColumnDimension('B')->setWidth(15); // № заказа
        $sheet->getColumnDimension('C')->setWidth(15); // Дата поездки
        $sheet->getColumnDimension('D')->setWidth(30);  // Откуда
        $sheet->getColumnDimension('E')->setWidth(30); // Куда
//        $sheet->getColumnDimension('G')->setWidth(30); // Обратно
//        $sheet->getColumnDimension('H')->setWidth(15); // Дата обратно
        $sheet->getColumnDimension('F')->setWidth(15); // Сотовый
        $sheet->getColumnDimension('G')->setWidth(10);  // Скидка
        $sheet->getColumnDimension('H')->setWidth(8); // Предв. дальность
        $sheet->getColumnDimension('I')->setWidth(15); // Цена за поездку
        $sheet->getColumnDimension('J')->setWidth(15); // Сумма к оплате
        $sheet->getColumnDimension('K')->setWidth(15); // Сумма к возмещению
        $sheet->getColumnDimension('L')->setWidth(20); // Категория
        $sheet->getColumnDimension('M')->setWidth(20); // Доп. сведения
//        $sheet->getColumnDimension('Q')->setWidth(40); // Группировка заказов
        
        // Устанавливаем перенос текста для столбцов "Откуда" (D) и "Куда" (E)
        $sheet->getStyle('D:E')->getAlignment()->setWrapText(true);
        
        // Устанавливаем динамическую высоту строк в зависимости от длины текста
        $startRow = 5; // данные начинаются с 5-й строки
        foreach ($this->orders as $index => $order) {
            $rowNumber = $startRow + $index;

            // Определяем длину текста в "Откуда" и "Куда"
            $fromAddressLength = strlen($order->adres_otkuda ?? '');
            $toAddressLength = strlen($order->adres_kuda ?? '');

            // Примерная логика: если текст длинный, увеличиваем высоту
            $baseHeight = 15; // Базовая высота
            $extraHeight = 0;

            if ($fromAddressLength > 35 || $toAddressLength > 35) {
                $extraHeight = 15; // Добавить высоту, если текст > 50 символов
            }
            if ($fromAddressLength > 70 || $toAddressLength > 70) {
                $extraHeight = 30; // Больше высоты, если > 100
            }
            if ($fromAddressLength > 100 || $toAddressLength > 100) {
                $extraHeight = 45; // И т.д.
            }

            $sheet->getRowDimension($rowNumber)->setRowHeight($baseHeight + $extraHeight);
        }

        
        
        // Подсветка сгруппированных заказов (кроме первого в группе)
//        $startRow = 5; // Начинаем с 5-й строки (после заголовков)
//        $currentGroup = null;
//
//        foreach ($this->orders as $index => $order) {
//            $rowNumber = $startRow + $index;
//            
//            if ($order->order_group_id && $order->order_group_id == $currentGroup) {
//                // Это не первый заказ в группе - выделяем серым
//                $sheet->getStyle("A{$rowNumber}:Q{$rowNumber}")->applyFromArray([
//                    'fill' => [
//                        'fillType' => Fill::FILL_SOLID,
//                        'color' => ['rgb' => 'F2F2F2'] // Светло-серый фон
//                    ]
//                ]);
//            } else {
//                // Это первый заказ в группе или одиночный - оставляем белым
//                $currentGroup = $order->order_group_id;
//            }
//        }

        return [];
    }

    public function columnFormats(): array
    {
        return [
            'F' => '@', // Телефон как текст
            'C' => '@', // № заказа как текст
//            'J' => '@', // Скидка как текст
//            'K' => '@', // Предв. дальность как текст
        ];
    }
}