<?php
// app/Http/Controllers/Admin/TaxiOrderImportController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Order;

class TaxiOrderImportController extends Controller
{
    public function showUploadForm()
    {
        return view('admin.taxi-orders.compare-upload');
    }

    public function compareAndImport(Request $request)
    {
        $request->validate([
            'original_file' => 'required|mimes:xlsx,xls,csv',
            'taxi_file' => 'required|mimes:xlsx,xls,csv',
        ]);

        $originalFile = $request->file('original_file');
        $taxiFile = $request->file('taxi_file');

        try {
            // Загружаем оба файла
            $originalSpreadsheet = IOFactory::load($originalFile->getPathname());
            $taxiSpreadsheet = IOFactory::load($taxiFile->getPathname());

            $originalSheet = $originalSpreadsheet->getActiveSheet();
            $taxiSheet = $taxiSpreadsheet->getActiveSheet();

            $changes = [];
            $errors = [];
            $rowIndex = 5; // Данные начинаются с 5-й строки

            while (true) {
                $originalOrderId = $originalSheet->getCell("B{$rowIndex}")->getValue(); // № заказа
                $taxiOrderId = $taxiSheet->getCell("B{$rowIndex}")->getValue(); // № заказа

                // Если в одном из файлов закончились строки
                if (!$originalOrderId && !$taxiOrderId) {
                    break;
                }

                // Проверяем, совпадают ли номера заказов
                if ($originalOrderId !== $taxiOrderId) {
                    $errors[] = [
                        'row' => $rowIndex,
                        'message' => "Номера заказов не совпадают: ваш файл - {$originalOrderId}, файл от такси - {$taxiOrderId}"
                    ];
                    $rowIndex++;
                    continue;
                }

                if (!$originalOrderId) {
                    $errors[] = [
                        'row' => $rowIndex,
                        'message' => "В вашем файле закончились заказы, но в файле от такси есть заказ {$taxiOrderId}"
                    ];
                    $rowIndex++;
                    continue;
                }

                if (!$taxiOrderId) {
                    $errors[] = [
                        'row' => $rowIndex,
                        'message' => "В файле от такси закончились заказы, но в вашем файле есть заказ {$originalOrderId}"
                    ];
                    $rowIndex++;
                    continue;
                }

                // Находим заказ в базе
                $order = Order::where('pz_nom', $originalOrderId)->first();

                if (!$order) {
                    $errors[] = [
                        'row' => $rowIndex,
                        'message' => "Заказ {$originalOrderId} не найден в системе"
                    ];
                    $rowIndex++;
                    continue;
                }

                // Сравниваем значения
                $originalPredvWay = $originalSheet->getCell("H{$rowIndex}")->getValue(); // Предв. дальность
                $taxiPredvWay = $taxiSheet->getCell("H{$rowIndex}")->getValue();

                $originalTaxiPrice = $originalSheet->getCell("I{$rowIndex}")->getValue(); // Цена за поездку
                $taxiTaxiPrice = $taxiSheet->getCell("I{$rowIndex}")->getValue();

                $originalTaxiVozm = $originalSheet->getCell("K{$rowIndex}")->getValue(); // Сумма к возмещению
                $taxiTaxiVozm = $taxiSheet->getCell("K{$rowIndex}")->getValue();

                $changeDetails = [];

                if ($taxiPredvWay != $originalPredvWay) {
                    $changeDetails[] = "Предв. дальность: {$originalPredvWay} → {$taxiPredvWay}";
                }
                if ($taxiTaxiPrice != $originalTaxiPrice) {
                    $changeDetails[] = "Цена за поездку: {$originalTaxiPrice} → {$taxiTaxiPrice}";
                }
                if ($taxiTaxiVozm != $originalTaxiVozm) {
                    $changeDetails[] = "Сумма к возмещению: {$originalTaxiVozm} → {$taxiTaxiVozm}";
                }

                if (!empty($changeDetails)) {
                    $changes[] = [
                        'row' => $rowIndex,
                        'order_id' => $order->id,
                        'pz_nom' => $originalOrderId,
                        'changes' => $changeDetails,
                        'type' => 'changed'
                    ];
                }

                $rowIndex++;
            }

            // Применяем изменения, если разрешено
            if ($request->has('apply_changes')) {
                foreach ($changes as $change) {
                    $order = Order::find($change['order_id']);

                    // Обновляем только разрешённые поля
                    $order->update([
                        'predv_way' => $taxiSheet->getCell("H{$change['row']}")->getValue(),
                        'taxi_price' => $taxiSheet->getCell("I{$change['row']}")->getValue(),
                        'taxi_vozm' => $taxiSheet->getCell("K{$change['row']}")->getValue(),
                    ]);
                }
            }

            return redirect()->back()
                ->with('success', 'Файлы успешно сравнены.')
                ->with('changes', $changes)
                ->with('errors', $errors);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Ошибка при обработке файлов: ' . $e->getMessage()]);
        }
    }
}