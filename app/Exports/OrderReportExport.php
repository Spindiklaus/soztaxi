<?php

namespace App\Exports;

use App\Services\OrderReportBuilder;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Carbon\Carbon;

class OrderReportExport implements FromArray, WithHeadings, WithEvents
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function array(): array
    {
        $report = (new OrderReportBuilder($this->startDate, $this->endDate))->build();
        
        // --- ВРЕМЕННАЯ ОТЛАДКА ---
        \Log::info('Отчет для экспорта:', $report->toArray());
        // -------------------------
        
        
        $rows = [];

        foreach ($report as $date => $data) {
            $data = (array)$data; // Преобразуем в массив, если объект
            foreach ($data['types'] as $typeId => $stats) {
                $rows[] = [
                    'visit_date' => Carbon::createFromFormat('Y-m-d', $date)->format('d.m.Y'), 
                    'type_order' => getOrderTypeName($typeId),
                    // Проверяем на null или 0 (число), а не на "ложность"
                    'status_1_count' => ($stats['status_1_count'] === null || $stats['status_1_count'] === 0) ? '' : $stats['status_1_count'],
                    'status_2_count' => ($stats['status_2_count'] === null || $stats['status_2_count'] === 0) ? '' : $stats['status_2_count'],
                    'status_3_count' => ($stats['status_3_count'] === null || $stats['status_3_count'] === 0) ? '' : $stats['status_3_count'],
                    'status_4_count' => ($stats['status_4_count'] === null || $stats['status_4_count'] === 0) ? '' : $stats['status_4_count'],
                ];
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Дата поездки',
            'Тип заказа',
            'Принят (id=1)',
            'Передан в такси (id=2)',
            'Отменен (id=3)',
            'Закрыт (id=4)',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Заголовок
                $sheet->setCellValue('A1', 'Сводка по статусам заказов');
                $sheet->mergeCells('A1:F1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Фильтры
                $start = Carbon::createFromFormat('Y-m-d', $this->startDate)->format('d.m.Y');
                $end = Carbon::createFromFormat('Y-m-d', $this->endDate)->format('d.m.Y');
                $sheet->setCellValue('A2', "Период: с {$start} по {$end}");
                $sheet->mergeCells('A2:F2');
                $sheet->getStyle('A2')->getFont()->setItalic(true);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
                
                 // Вставляем заголовки таблицы в строку 3
                $sheet->setCellValue('A3', $this->headings()[0]);
                $sheet->setCellValue('B3', $this->headings()[1]);
                $sheet->setCellValue('C3', $this->headings()[2]);
                $sheet->setCellValue('D3', $this->headings()[3]);
                $sheet->setCellValue('E3', $this->headings()[4]);
                $sheet->setCellValue('F3', $this->headings()[5]);
                
                //                // Шапка таблицы (строка 3)
//                $sheet->getStyle('A3:F3')->getFont()->setBold(true);
//                $sheet->getStyle('A3:F3')->getFill()
//                    ->setFillType(Fill::FILL_SOLID)
//                    ->getStartColor()->setRGB('D3D3D3'); // Светло-серый фон
//
//                // Рамки для шапки
//                $sheet->getStyle('A3:F3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                

                // Стили для строки заголовков (строка 3)
                $sheet->getStyle('A3:F3')->getFont()->setBold(true);
                $sheet->getStyle('A3:F3')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('D3D3D3'); // Светло-серый фон

                // Рамки для шапки
                $sheet->getStyle('A3:F3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Установка ширины колонок
                $sheet->getColumnDimension('A')->setWidth(15); // Дата поездки
                $sheet->getColumnDimension('B')->setWidth(15); // Тип заказа
                $sheet->getColumnDimension('C')->setWidth(20); // Принят
                $sheet->getColumnDimension('D')->setWidth(25); // Передан в такси
                $sheet->getColumnDimension('E')->setWidth(20); // Отменен
                $sheet->getColumnDimension('F')->setWidth(20); // Закрыт


                // Рамки для данных (начиная с 4 строки)
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle("A5:F{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            },
        ];
    }
}