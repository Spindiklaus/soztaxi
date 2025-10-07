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

class OrderReportExport implements FromArray, WithEvents
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
        \Log::info('Отчет для экспорта (до формирования rows):', $report->toArray());
        // -----------------
        
        
        $rows = [];
        // --- ДОБАВЛЯЕМ ЗАГЛАВИЕ И ФИЛЬТРЫ КАК ПЕРВЫЕ СТРОКИ ДАННЫХ ---
        // Строка 1: Заголовок отчета
        $rows[] = [
            'visit_date' => 'Сводка по статусам заказов',
            'type_order' => '',
            'status_1_count' => '',
            'status_2_count' => '',
            'status_3_count' => '',
            'status_4_count' => '',
        ];

        // Строка 2: Период фильтрации
        $start = Carbon::createFromFormat('Y-m-d', $this->startDate)->format('d.m.Y');
        $end = Carbon::createFromFormat('Y-m-d', $this->endDate)->format('d.m.Y');
        $rows[] = [
            'visit_date' => "Период: с {$start} по {$end}",
            'type_order' => '',
            'status_1_count' => '',
            'status_2_count' => '',
            'status_3_count' => '',
            'status_4_count' => '',
        ];

        // Строка 3: Заголовки столбцов
        $rows[] = [
            'visit_date' => 'Дата поездки', // Явно указываем, без использования headings()
            'type_order' => 'Тип заказа',
            'status_1_count' => 'Принят ',
            'status_2_count' => 'Передан в такси ',
            'status_3_count' => 'Отменен',
            'status_4_count' => 'Закрыт',
        ];

  
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
        
        // --- ВРЕМЕННАЯ ОТЛАДКА ---
        \Log::info('Сформированные строки для Excel (rows):', $rows);
        // -------------------------

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // Установка ширины колонок
                $sheet->getColumnDimension('A')->setWidth(15); // Дата поездки
                $sheet->getColumnDimension('B')->setWidth(15); // Тип заказа
                $sheet->getColumnDimension('C')->setWidth(20); // Принят
                $sheet->getColumnDimension('D')->setWidth(25); // Передан в такси
                $sheet->getColumnDimension('E')->setWidth(20); // Отменен
                $sheet->getColumnDimension('F')->setWidth(20); // Закрыт

                // Рамки для данных (начиная с 4 строки)
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle("A4:F{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // --- ЯВНОЕ УКАЗАНИЕ ФОРМАТА КОЛОНКИ A КАК ТЕКСТ ---
                // Применяем к диапазону данных, начиная с 4 строки
                $sheet->getStyle("A4:A{$lastRow}")->getNumberFormat()->setFormatCode('@');
                // --- Конец кода форматирования ---

                // --- СТИЛИ ДЛЯ ЗАГОЛОВКОВ (строки 1, 2, 3) ---
                // Строка 1: Заголовок отчета
                $sheet->getStyle('A1:F1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1:F1')->getAlignment()->setHorizontal('left');

                // Строка 2: Период фильтрации
                $sheet->getStyle('A2:F2')->getFont()->setItalic(true);
                $sheet->getStyle('A2:F2')->getAlignment()->setHorizontal('left');

                // Строка 3: Заголовки столбцов
                $sheet->getStyle('A3:F3')->getFont()->setBold(true);
                $sheet->getStyle('A3:F3')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('D3D3D3'); // Светло-серый фон
                $sheet->getStyle('A3:F3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // --- Конец стилей заголовков ---
            },
        ];
    }
}