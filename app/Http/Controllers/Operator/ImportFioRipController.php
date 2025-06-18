<?php

namespace App\Http\Controllers\Operator;

use Illuminate\Http\Request;
use App\Models\FioRip;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ImportFioRipController extends BaseController {

    // Форма импорта
    public function showImportForm() {
        return view('fio_rips.import');
    }

    // Обработка импорта
    public function import(Request $request) {
        // Валидация файла
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $handle = fopen($path, 'r');

        // Чтение заголовка
        $header = fgetcsv($handle, 0, ';'); // Указываем разделитель ;
        $header = array_map('trim', $header); // Очищаем заголовок
        // Разрешённые заголовки
        $allowedHeaders = ['fio', 'kl_id', 'data_r', 'sex', 'adres', 'rip_at', 'nom_zap'];

        // Проверка заголовка
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

        // Размер пакета
        $batchSize = 1000; // Количество записей в одном пакете
        $batch = [];

        foreach ($rows as $index => $row) {
            // Проверка количества столбцов
            if (count($row) < count($allowedHeaders)) {
                $errors[] = "Строка " . ($index + 2) . ": недостаточно данных";
                continue;
            }

            // Сопоставление данных с заголовками
            $data = array_combine($allowedHeaders, $row);

            try {
                // Валидация данных
                Validator::make($data, [
                    'kl_id' => 'nullable|string|max:255',
                    'fio' => 'required|string|max:255',
                    'data_r' => 'nullable|date_format:d.m.Y',
                    'sex' => 'nullable|in:М,Ж',
                    'adres' => 'nullable|string|max:255',
                    'rip_at' => 'required|date_format:d.m.Y',
                    'nom_zap' => 'string|max:255',
                ])->validate();

                // Преобразование дат
                $data['data_r'] = $data['data_r'] ? Carbon::createFromFormat('d.m.Y', $data['data_r'])->toDateString() : null;
                $data['rip_at'] = $data['rip_at'] ? Carbon::createFromFormat('d.m.Y', $data['rip_at'])->startOfDay() : null;

                // Проверка дубликата по kl_id, fio, data_r и rip_at
                if (FioRip::where('kl_id', $data['kl_id'])
                                ->where('fio', $data['fio'])
                                ->where('data_r', $data['data_r'])
                                ->where('rip_at', $data['rip_at'])->exists()) {
                    $errors[] = "Строка " . ($index + 2) . ": запись с такими данными уже существует.";
                    continue;
                }


//                // Создание записи
//                FioRip::create(array_merge($data, [
//                    'user_id' => auth()->id(), // Используем ID текущего пользователя
//                ]));                
//                $successCount++;
//                Log::info("Запись успешно импортирована", ['kl_id' => $data['kl_id'], 'fio' => $data['fio']]);
                // Добавляем данные в пакет
                $batch[] = array_merge($data, [
                    'user_id' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                // Если пакет заполнен, сохраняем его
                if (count($batch) === $batchSize) {
                    FioRip::insert($batch);
                    $batch = [];
                    $successCount += $batchSize;
                }
            } catch (\Exception $e) {
                $errors[] = "Строка " . ($index + 2) . ": " . $e->getMessage();
//                Log::error("Ошибка при импорте строки", ['row' => $row, 'error' => $e->getMessage()]);
            }
        }

        // Сохраняем оставшиеся записи
        if (!empty($batch)) {
            FioRip::insert($batch);
            $successCount += count($batch);
        }

        // Возвращаем результат
        if (!empty($errors)) {
            return back()
                            ->with('import_errors', $errors)
                            ->with('success_count', $successCount);
        }

        Log::info("Импорт завершён успешно", ['success_count' => $successCount]);
        return redirect()->route('fio_rips.index')
                        ->with('success', "Успешно импортировано: {$successCount} записей");
    }

}
