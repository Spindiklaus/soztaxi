<?php
// app/Services/TaxiOrderImportService.php
// сравнение файлов наш и из такси

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Order;

class TaxiOrderImportService
{
    /**
     * Сравнить загруженный файл с оригинальным и вернуть список изменений
     */
    public function compareWithOriginal($uploadedFilePath, $originalFilePath)
    {
        // Загружаем оба файла
        $uploaded = IOFactory::load($uploadedFilePath);
        $original = IOFactory::load($originalFilePath);

        $uploadedSheet = $uploaded->getActiveSheet();
        $originalSheet = $original->getActiveSheet();

        $changes = [];
        $rowIndex = 5; // Предполагаем, что данные начинаются с 5-й строки

        while ($uploadedSheet->getCell("A{$rowIndex}")->getValue()) {
            $orderIdCell = $uploadedSheet->getCell("B{$rowIndex}"); // № заказа в столбце B
            $orderPzNom = $orderIdCell->getValue();

            // Находим заказ по номеру
            $order = Order::where('pz_nom', $orderPzNom)->first();

            if (!$order) {
                $changes[] = [
                    'row' => $rowIndex,
                    'type' => 'error',
                    'message' => "Заказ {$orderPzNom} не найден в системе."
                ];
                $rowIndex++;
                continue;
            }

            // Получаем значения из загруженного файла
            $predvWay = $uploadedSheet->getCell("H{$rowIndex}")->getValue(); // Предв. дальность
            $taxiPrice = $uploadedSheet->getCell("I{$rowIndex}")->getValue(); // Цена за поездку
            $taxiVozm = $uploadedSheet->getCell("K{$rowIndex}")->getValue(); // Сумма к возмещению

            // Получаем значения из оригинального файла
            $originalPredvWay = $originalSheet->getCell("H{$rowIndex}")->getValue();
            $originalTaxiPrice = $originalSheet->getCell("I{$rowIndex}")->getValue();
            $originalTaxiVozm = $originalSheet->getCell("K{$rowIndex}")->getValue();

            // Проверяем изменения
            $changeDetails = [];

            if ($predvWay != $originalPredvWay) {
                $changeDetails[] = "Предв. дальность: {$originalPredvWay} → {$predvWay}";
            }
            if ($taxiPrice != $originalTaxiPrice) {
                $changeDetails[] = "Цена за поездку: {$originalTaxiPrice} → {$taxiPrice}";
            }
            if ($taxiVozm != $originalTaxiVozm) {
                $changeDetails[] = "Сумма к возмещению: {$originalTaxiVozm} → {$taxiVozm}";
            }

            if (!empty($changeDetails)) {
                $changes[] = [
                    'row' => $rowIndex,
                    'order_id' => $order->id,
                    'pz_nom' => $orderPzNom,
                    'changes' => $changeDetails,
                    'type' => 'changed'
                ];
            }

            $rowIndex++;
        }

        return $changes;
    }

    /**
     * Применить изменения из файла к заказам
     */
    public function applyChanges($uploadedFilePath, $originalFilePath)
    {
        $changes = $this->compareWithOriginal($uploadedFilePath, $originalFilePath);

        foreach ($changes as $change) {
            if ($change['type'] == 'changed') {
                $order = Order::find($change['order_id']);

                // Обновляем только разрешённые поля
                $order->update([
                    'predv_way' => $uploadedSheet->getCell("H{$change['row']}")->getValue(),
                    'taxi_price' => $uploadedSheet->getCell("I{$change['row']}")->getValue(),
                    'taxi_vozm' => $uploadedSheet->getCell("K{$change['row']}")->getValue(),
                ]);
            }
        }

        return $changes;
    }
}