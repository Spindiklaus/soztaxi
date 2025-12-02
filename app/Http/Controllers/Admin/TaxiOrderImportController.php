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

        // --- Подсчёт итогов в оригинальном файле ---
        $originalTotalPredvWay = 0;
        $originalTotalTaxiPrice = 0;
        $originalTotalTaxiOpl = 0;
        $originalTotalTaxiVozm = 0;

        $origRow = 5;
        while (true) {
            $originalOrderId = $originalSheet->getCell("B{$origRow}")->getValue();
            if (!$originalOrderId) break;

            $originalTotalPredvWay += (float) $originalSheet->getCell("H{$origRow}")->getValue();
            $originalTotalTaxiPrice += (float) $originalSheet->getCell("I{$origRow}")->getValue();
            $originalTotalTaxiOpl += (float) $originalSheet->getCell("J{$origRow}")->getValue();
            $originalTotalTaxiVozm += (float) $originalSheet->getCell("K{$origRow}")->getValue();

            $origRow++;
        }

        // --- Подсчёт итогов в файле от такси ---
        $taxiTotalPredvWay = 0;
        $taxiTotalTaxiPrice = 0;
        $taxiTotalTaxiOpl = 0;
        $taxiTotalTaxiVozm = 0;

        $taxiRow = 5;
        while (true) {
            $taxiOrderId = $taxiSheet->getCell("B{$taxiRow}")->getValue();
            if (!$taxiOrderId) break;

            $taxiTotalPredvWay += (float) $taxiSheet->getCell("H{$taxiRow}")->getValue();
            $taxiTotalTaxiPrice += (float) $taxiSheet->getCell("I{$taxiRow}")->getValue();
            $taxiTotalTaxiOpl += (float) $taxiSheet->getCell("J{$taxiRow}")->getValue();
            $taxiTotalTaxiVozm += (float) $taxiSheet->getCell("K{$taxiRow}")->getValue();

            $taxiRow++;
        }

        // --- Сравнение итогов ---
        $totalChanges = [];
        if (abs($taxiTotalPredvWay - $originalTotalPredvWay) > 0.01) {
            $totalChanges[] = "Итог предв. дальности: {$originalTotalPredvWay} → {$taxiTotalPredvWay}";
        }
        if (abs($taxiTotalTaxiPrice - $originalTotalTaxiPrice) > 0.01) {
            $totalChanges[] = "Итог цены за поездку: {$originalTotalTaxiPrice} → {$taxiTotalTaxiPrice}";
        }
        if (abs($taxiTotalTaxiOpl - $originalTotalTaxiOpl) > 0.01) {
            $totalChanges[] = "Итог к оплате: {$originalTotalTaxiOpl} → {$taxiTotalTaxiOpl}";
        }
        if (abs($taxiTotalTaxiVozm - $originalTotalTaxiVozm) > 0.01) {
            $totalChanges[] = "Итог суммы к возмещению: {$originalTotalTaxiVozm} → {$taxiTotalTaxiVozm}";
        }

        // --- Сравнение строк ---
        $rowIndex = 5;
        while (true) {
            $taxiOrderId = $taxiSheet->getCell("B{$rowIndex}")->getValue(); // № заказа из файла такси

            if (!$taxiOrderId) {
                break; // Закончились строки в файле такси
            }

            // Проверим, есть ли этот заказ в файле "оригинал"
            $originalRow = null;
            $origRow = 5;
            while (true) {
                $originalOrderId = $originalSheet->getCell("B{$origRow}")->getValue();
                if (!$originalOrderId) {
                    break; // Закончились строки в оригинале
                }
                if ($originalOrderId === $taxiOrderId) {
                    $originalRow = $origRow;
                    break;
                }
                $origRow++;
            }

            if ($originalRow) {
                // Заказ найден в оригинале — сравниваем
                $order = Order::where('pz_nom', $taxiOrderId)->first();

                if (!$order) {
                    $errors[] = [
                        'row' => $rowIndex,
                        'message' => "Заказ {$taxiOrderId} найден в файле такси, но не найден в системе"
                    ];
                    $rowIndex++;
                    continue;
                }

                // Сравниваем значения
                $originalPredvWay = $originalSheet->getCell("H{$originalRow}")->getValue();
                $taxiPredvWay = $taxiSheet->getCell("H{$rowIndex}")->getValue();

                $originalTaxiPrice = $originalSheet->getCell("I{$originalRow}")->getValue();
                $taxiTaxiPrice = $taxiSheet->getCell("I{$rowIndex}")->getValue();

                $originalTaxiOpl = $originalSheet->getCell("J{$originalRow}")->getValue();
                $taxiTaxiOpl = $taxiSheet->getCell("J{$rowIndex}")->getValue();

                $originalTaxiVozm = $originalSheet->getCell("K{$originalRow}")->getValue();
                $taxiTaxiVozm = $taxiSheet->getCell("K{$rowIndex}")->getValue();

                $changeDetails = [];

                if ($taxiPredvWay != $originalPredvWay) {
                    $changeDetails[] = "Предв. дальность: {$originalPredvWay} → {$taxiPredvWay}";
                }
                if ($taxiTaxiPrice != $originalTaxiPrice) {
                    $changeDetails[] = "Цена за поездку: {$originalTaxiPrice} → {$taxiTaxiPrice}";
                }
                if ($taxiTaxiOpl != $originalTaxiOpl) {
                    $changeDetails[] = "К оплате: {$originalTaxiOpl} → {$taxiTaxiOpl}";
                }
                if ($taxiTaxiVozm != $originalTaxiVozm) {
                    $changeDetails[] = "Сумма к возмещению: {$originalTaxiVozm} → {$taxiTaxiVozm}";
                }

                if (!empty($changeDetails)) {
                    $changes[] = [
                        'row' => $rowIndex,
                        'order_id' => $order->id,
                        'pz_nom' => $taxiOrderId,
                        'changes' => $changeDetails,
                        'type' => 'changed'
                    ];
                }
            } else {
                // Заказа нет в оригинале — это добавление
                $errors[] = [
                    'row' => $rowIndex,
                    'message' => "Заказ {$taxiOrderId} добавлен оператором такси (не был в вашем файле)"
                ];
            }

            $rowIndex++;
        }

        return redirect()->back()
            ->with('success', 'Файлы успешно сравнены.')
            ->with('changes', $changes)
            ->with('comparison_errors', $errors)
            ->with('total_changes', $totalChanges);

    } catch (\Exception $e) {
        return redirect()->back()->withErrors(['error' => 'Ошибка при обработке файлов: ' . $e->getMessage()]);
    }
}
}