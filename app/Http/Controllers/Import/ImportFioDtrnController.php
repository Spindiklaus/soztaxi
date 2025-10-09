<?php

namespace App\Http\Controllers\Import;

use Illuminate\Http\Request;
use App\Models\FioDtrn;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ImportFioDtrnController extends BaseController {

    // Форма импорта клиентов
    public function showClientsImportForm() {
        return view('fiodtrns.import');
    }

    // Обработка импорта клиентов
    public function importClients(Request $request) {
        // Проверка файла
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        Log::info("Файл загружен", ['path' => $path]);

        // Читаем содержимое и удаляем BOM
        $content = file_get_contents($path);
        $content = $this->removeBOM($content);
        file_put_contents($path, $content);

        $rows = [];
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return back()->with('error', 'Не удалось открыть файл');
        }

        // Чтение заголовка
        $header = fgetcsv($handle, 0, ';');

        if ($header === false) {
            fclose($handle);
            return back()->with('import_errors', ['Не удалось прочитать заголовок CSV файла.']);
        }

        // Нормализуем заголовок
        $header = array_map('trim', $header);

        Log::info("Полученный заголовок", ['header' => $header]);

        // Разрешённые заголовки
        $allowedHeaders = ['kl_id', 'fio', 'data_r', 'sex', 'rip_at', 'created_rip', 'komment'];

        Log::info("Ожидаемый заголовок", ['expected' => $allowedHeaders]);

        // Проверяем заголовок
        if ($header !== $allowedHeaders) {
            Log::error("Неверный заголовок CSV", [
                'expected' => implode(';', $allowedHeaders),
                'got' => implode(';', $header)
            ]);
            fclose($handle);
            return back()->with('import_errors', [
                        'Неверный формат CSV. Заголовки должны быть: ' . implode(';', $allowedHeaders),
                        'Получено: ' . implode(';', $header)
            ]);
        }

        // Чтение строк данных
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        $errors = [];
        $successCount = 0;

        foreach ($rows as $index => $row) {
            if (count($row) < count($allowedHeaders)) {
                $errorMsg = "Строка " . ($index + 2) . ": недостаточно данных";
                Log::warning($errorMsg);
                $errors[] = $errorMsg;
                continue;
            }

            // Сопоставление данных
            $data = array_combine($allowedHeaders, $row);

            // Нормализуем данные
            foreach ($data as $key => $value) {
                // Убираем специальные символы и приводим к NULL если нужно
                if (in_array($value, ['  -   -  : :', '-', '0', ':', '  -   -  :', '  -  : :', '  -   -', ''])) {
                    $data[$key] = null;
                }
                else {
                    $data[$key] = trim($value);
                }
            }

            try {
                Validator::make($data, [
                    'kl_id' => 'required|string|max:255',
                    'fio' => 'required|string|max:255',
                    'data_r' => 'nullable|date_format:d.m.Y',
                    'sex' => 'nullable|in:М,Ж',
                    'rip_at' => 'nullable|date_format:d.m.Y',
                    'created_rip' => [
                        'nullable',
                        function ($attribute, $value, $fail) {
                            if (empty($value)) {
                                return; // Пропускаем, если null
                            }

                            $formats = ['d.m.Y H:i', 'd.m.Y G:i'];

                            foreach ($formats as $format) {
                                $date = \DateTime::createFromFormat($format, $value);
                                if ($date && $date->format($format) === $value) {
                                    return; // Успешно прошло валидацию
                                }
                            }

                            $fail("Поле {$attribute} должно быть в формате d.m.Y H:i или d.m.Y G:i.");
                        },
                    ],
                    'komment' => 'nullable|string',
                ])->validate();

                // Преобразование дат
                if (!empty($data['data_r'])) {
                    $data['data_r'] = Carbon::createFromFormat('d.m.Y', $data['data_r'])->format('Y-m-d');
                }
                if (!empty($data['rip_at'])) {
                    $data['rip_at'] = Carbon::createFromFormat('d.m.Y', $data['rip_at'])->format('Y-m-d');
                }
                if (!empty($data['created_rip'])) {
                    $data['created_rip'] = Carbon::createFromFormat('d.m.Y H:i', $data['created_rip'])->format('Y-m-d H:i:s');
                }
                // Проверка дубликата
                if (FioDtrn::where('kl_id', $data['kl_id'])->exists()) {
                    $errorMsg = "Строка " . ($index + 2) . ": клиент с ID {$data['kl_id']} уже существует.";
                    Log::info($errorMsg);
                    $errors[] = $errorMsg;
                    continue;
                }

                // Сохранение клиента
                FioDtrn::create(array_merge($data, [
                    'user_id' => auth()->id() ?? 1,
                ]));

                $successCount++;
                Log::info("Клиент импортирован", ['kl_id' => $data['kl_id'], 'fio' => $data['fio']]);
            } catch (ValidationException $e) {
                $errorMsg = "Строка " . ($index + 2) . ": " . collect($e->validator->errors()->all())->join(', ');
                Log::warning($errorMsg);
                $errors[] = $errorMsg;
            } catch (\Throwable $e) {
                $errorMsg = "Неизвестная ошибка на строке " . ($index + 2) . ": " . $e->getMessage();
                Log::error($errorMsg);
                $errors[] = $errorMsg;
            }
        }

        if (!empty($errors)) {
            return back()
                            ->with('import_errors', $errors)
                            ->with('success_count', $successCount);
        }

        Log::info("Импорт завершён успешно", ['success_count' => $successCount]);
        return redirect()->route('fiodtrns.index')
                        ->with('success', "Успешно импортировано: {$successCount} клиент(ов)");
    }

    /**
     * Удаление BOM символа
     */
    private function removeBOM($str) {
        // Удаляем UTF-8 BOM если есть
        if (substr($str, 0, 3) === "\xEF\xBB\xBF") {
            $str = substr($str, 3);
        }
        return $str;
    }

}
