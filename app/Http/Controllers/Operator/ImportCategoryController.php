<?php

namespace App\Http\Controllers\Operator;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportCategoryController extends BaseController {

    // Показываем форму импорта
    public function showImportForm() {
        return view('categories.import');
    }

    public function import(Request $request) {
        // Валидация файла
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $handle = fopen($path, 'r');

        // Чтение заголовка
        $header = fgetcsv($handle, 0, ';'); // Указываем разделитель ;
        $header = array_map('trim', $header);

        // Разрешённые заголовки
        $allowedHeaders = ['name', 'nmv', 'skidka', 'kol_p', 'is_soz', 'is_auto', 'is_gaz', 'kat_dop'];

        if ($header !== $allowedHeaders) {
            Log::error("Неверный заголовок CSV", ['expected' => $allowedHeaders, 'got' => $header]);
            return back()->with('import_errors', ['Неверный заголовок CSV. Должен быть: ' . implode(';', $allowedHeaders)]);
        }

        $rows = [];
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        $errors = [];
        $successCount = 0;
        $batchSize = 1000; // Размер пакета
        $batch = [];

        foreach ($rows as $index => $row) {
            if (count($row) < count($allowedHeaders)) {
                $errors[] = "Строка " . ($index + 2) . ": недостаточно данных";
                continue;
            }

            // Сопоставление данных
            $data = array_combine($allowedHeaders, $row);

            try {
                Validator::make($data, [
                    'name' => 'required|string|max:255',
                    'nmv' => 'required|integer|min:1000|max:999999',
                    'skidka' => 'required|integer|min:0|max:100',
                    'kol_p' => 'required|integer|min:0',
                    'is_soz' => 'required|in:0,1',
                    'is_auto' => 'required|in:0,1',
                    'is_gaz' => 'required|in:0,1',
                    'kat_dop' => 'required|in:0,1,2',
                ])->validate();

                // Проверка на дубликат по полю nvm
                if (Category::where('nmv', $data['nmv'])->exists()) {
                    $errors[] = "Строка " . ($index + 2) . ": NMV {$data['nmv']} уже существует.";
                    continue;
                }


                // Добавляем данные в пакет
                $batch[] = [
                    'name' => $data['name'],
                    'nmv' => $data['nmv'],
                    'skidka' => $data['skidka'],
                    'kol_p' => $data['kol_p'],
                    'is_soz' => $data['is_soz'],
                    'is_auto' => $data['is_auto'],
                    'is_gaz' => $data['is_gaz'],
                    'kat_dop' => $data['kat_dop'],
                    'user_id' => auth()->id(), // Текущий пользователь — оператор
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Если пакет заполнен, сохраняем его
                if (count($batch) === $batchSize) {
                    Category::insert($batch);
                    $batch = [];
                    $successCount += $batchSize;
                }
            } catch (\Exception $e) {
                $errors[] = "Строка " . ($index + 2) . ": " . $e->getMessage();
                Log::error("Ошибка при импорте строки", ['row' => $row, 'error' => $e->getMessage()]);
            }
        }

        // Сохраняем оставшиеся записи
        if (!empty($batch)) {
            Category::insert($batch);
            $successCount += count($batch);
        }

        if (!empty($errors)) {
            return back()
                            ->with('import_errors', $errors)
                            ->with('success_count', $successCount);
        }

        return redirect()->route('categories.index')
                        ->with('success', "Успешно импортировано: {$successCount} записей");
    }

}
