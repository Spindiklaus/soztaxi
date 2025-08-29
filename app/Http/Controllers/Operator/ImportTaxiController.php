<?php

namespace App\Http\Controllers\Operator;

use Illuminate\Http\Request;
use App\Models\Taxi;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ImportTaxiController extends BaseController {

    // Показываем форму импорта
    public function showImportForm() {
        return view('taxis.import');
    }

    public function import(Request $request) {
        // Валидация файла
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        
        // Читаем файл и удаляем BOM если есть
        $content = file_get_contents($path);
        $content = $this->removeBOMFromString($content);
        file_put_contents($path, $content);
        
        $handle = fopen($path, 'r');

        // Чтение заголовка
        $header = fgetcsv($handle, 0, ';'); // Указываем разделитель ;
        
        if ($header === false) {
            return back()->with('import_errors', ['Не удалось прочитать заголовок CSV файла.']);
        }
        
        // Нормализуем заголовки - убираем BOM и специальные символы
        $header = array_map(function($item) {
            // Убираем BOM если есть
            $item = $this->removeBOMFromString($item);
            // Trim whitespace
            $item = trim($item);
            return $item;
        }, $header);

        // Разрешённые заголовки (11 полей)
        $allowedHeaders = ['name', 'id', 'koef', 'posadka', 'koef50', 'posadka50', 'zena1_auto', 'zena2_auto', 'zena1_gaz', 'zena2_gaz', 'life'];

        // Проверяем количество полей
        if (count($header) !== count($allowedHeaders)) {
            return back()->with('import_errors', [
                'Неверное количество полей в заголовке. Получено: ' . count($header) . ', ожидается: ' . count($allowedHeaders)
            ]);
        }

        // Проверяем соответствие заголовков
        for ($i = 0; $i < count($header); $i++) {
            $cleanHeader = strtolower(trim($header[$i]));
            $cleanExpected = strtolower($allowedHeaders[$i]);
            if ($cleanHeader !== $cleanExpected) {
                return back()->with('import_errors', [
                    'Неверный заголовок CSV.',
                    'Поле ' . ($i + 1) . ': получено "' . $header[$i] . '", ожидается "' . $allowedHeaders[$i] . '"'
                ]);
            }
        }

        $rows = [];
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        $errors = [];
        $successCount = 0;
        $batch = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                if (count($row) < count($allowedHeaders)) {
                    $errors[] = "Строка " . ($index + 2) . ": недостаточно данных (получено " . count($row) . " полей)";
                    continue;
                }

                // Сопоставление данных
                $data = array_combine($allowedHeaders, $row);

                try {
                    Validator::make($data, [
                        'name' => 'required|string|max:255',
                        'id' => 'nullable|integer',
                        'koef' => 'required|string',
                        'posadka' => 'required|string',
                        'koef50' => 'required|string',
                        'posadka50' => 'required|string',
                        'zena1_auto' => 'required|string',
                        'zena2_auto' => 'required|string',
                        'zena1_gaz' => 'required|string',
                        'zena2_gaz' => 'required|string',
                        'life' => 'required|in:0,1',
                    ])->validate();

                    // Преобразуем запятые в точки для числовых значений
                    $numericFields = ['koef', 'posadka', 'koef50', 'posadka50', 'zena1_auto', 'zena2_auto', 'zena1_gaz', 'zena2_gaz'];
                    foreach ($numericFields as $field) {
                        if (isset($data[$field])) {
                            $data[$field] = str_replace(',', '.', $data[$field]);
                        }
                    }

                    // Проверка на дубликат по ID
                    if (!empty($data['id']) && Taxi::where('id', $data['id'])->exists()) {
                        $errors[] = "Строка " . ($index + 2) . ": Оператор такси с ID={$data['id']} уже существует.";
                        continue;
                    }

                    // Проверка на дубликат по имени
                    if (!empty($data['name']) && Taxi::where('name', $data['name'])->exists()) {
                        $errors[] = "Строка " . ($index + 2) . ": Оператор такси с названием '{$data['name']}' уже существует.";
                        continue;
                    }

                    // Подготавливаем данные для вставки
                    $taxiData = [
                        'name' => $data['name'],
                        'koef' => (float)$data['koef'],
                        'posadka' => (float)$data['posadka'],
                        'koef50' => (float)$data['koef50'],
                        'posadka50' => (float)$data['posadka50'],
                        'zena1_auto' => (float)$data['zena1_auto'],
                        'zena2_auto' => (float)$data['zena2_auto'],
                        'zena1_gaz' => (float)$data['zena1_gaz'],
                        'zena2_gaz' => (float)$data['zena2_gaz'],
                        'life' => (int)$data['life'],
                        'user_id' => auth()->id(), // Текущий пользователь — оператор
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // Если указан ID, используем его
                    if (!empty($data['id'])) {
                        $taxiData['id'] = (int)$data['id'];
                    }

                    $batch[] = $taxiData;

                    // Если пакет заполнен, сохраняем его
                    if (count($batch) >= 1000) {
                        foreach ($batch as $taxiRecord) {
                            DB::table('taxis')->insert($taxiRecord);
                        }
                        $successCount += count($batch);
                        $batch = [];
                    }

                } catch (\Exception $e) {
                    $errors[] = "Строка " . ($index + 2) . ": " . $e->getMessage();
                    Log::error("Ошибка при импорте строки", [
                        'row' => $row, 
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            // Сохраняем оставшиеся записи
            if (!empty($batch)) {
                foreach ($batch as $taxiRecord) {
                    DB::table('taxis')->insert($taxiRecord);
                }
                $successCount += count($batch);
            }

            DB::commit();

            if (!empty($errors)) {
                return back()
                                ->with('import_errors', $errors)
                                ->with('success_count', $successCount);
            }

            return redirect()->route('taxis.index')
                            ->with('success', "Успешно импортировано: {$successCount} записей");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Ошибка при импорте", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('import_errors', ['Ошибка при импорте: ' . $e->getMessage()]);
        }
    }

    /**
     * Удаление BOM символа из строки
     */
    private function removeBOMFromString($str) {
        // Удаляем UTF-8 BOM если есть
        if (substr($str, 0, 3) === "\xEF\xBB\xBF") {
            $str = substr($str, 3);
        }
        return $str;
    }
}