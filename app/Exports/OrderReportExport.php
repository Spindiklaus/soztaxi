<?php

namespace App\Exports;

use App\Services\OrderReportBuilder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Carbon\Carbon;


class OrderReportExport implements FromQuery, WithHeadings, WithMapping, WithEvents
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function query()
    {
        return (new OrderReportBuilder($this->startDate, $this->endDate))->build();
    }

    public function headings(): array
    {
        return [
            'Дата поездки',
            'Принят (id=1)',
            'Передан в такси (id=2)',
            'Отменен (id=3)',
            'Закрыт (id=4)',
        ];
    }

    public function map($row): array
    {
        return [
            $row->visit_date ? Carbon::parse($row->visit_date)->format('d.m.Y') : 'Не указана',
            $row->status_1_count,
            $row->status_2_count,
            $row->status_3_count,
            $row->status_4_count,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Заголовок
                $sheet->setCellValue('A1', 'Сводка по статусам заказов');
                $sheet->mergeCells('A1:E1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Фильтры
                $sheet->setCellValue('A2', "Период: с {$this->startDate} по {$this->endDate}");
                $sheet->mergeCells('A2:E2');
                $sheet->getStyle('A2')->getFont()->setItalic(true);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // Установка ширины колонок
                $sheet->getColumnDimension('A')->setWidth(15); // Дата поездки
                $sheet->getColumnDimension('B')->setWidth(20); // Принят
                $sheet->getColumnDimension('C')->setWidth(25); // Передан в такси
                $sheet->getColumnDimension('D')->setWidth(20); // Отменен
                $sheet->getColumnDimension('E')->setWidth(20); // Закрыт

                // Шапка таблицы (строка 3)
                $sheet->getStyle('A3:E3')->getFont()->setBold(true);
                $sheet->getStyle('A3:E3')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('D3D3D3'); // Светло-серый фон

                // Рамки для шапки
                $sheet->getStyle('A3:E3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Рамки для данных (начиная с 4 строки)
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle("A4:E{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            },
        ];
    }
}