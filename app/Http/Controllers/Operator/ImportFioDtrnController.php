<?php

namespace App\Http\Controllers\Operator;

use Illuminate\Http\Request;
use App\Models\FioDtrn;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // включаем логирование

class ImportFioDtrnController extends BaseController
{
 // Форма импорта клиентов
    public function showClientsImportForm()
    {
//        dd("Форма импорта открыта!");
        return view('fiodtrns.import');
    }

    // Обработка импорта клиентов
    public function importClients(Request $request)
    {
        // Проверка файла
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt',
        ]);
        $path = $request->file('csv_file')->getRealPath();
        Log::info("Файл загружен", ['path' => $path]); // 📌 Запись в лог
//        dd( $path);        
        
        $rows = [];
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return back()->with('error', 'Не удалось открыть файл');
        }
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $rows[] = $row;
        }
        fclose($handle);
        $header = array_shift($rows); // Убираем заголовок

        $allowedHeaders = ['kl_id', 'fio', 'data_r', 'sex', 'rip_at', 'created_rip', 'komment'];

        if ($header !== $allowedHeaders && $header !== [implode(';', $allowedHeaders)]) {
            Log::error("Неверный заголовок CSV", ['expected' => $allowedHeaders, 'got' => $header]);
            return back()->with('import_errors', ['Неверный формат CSV. Заголовки должны быть: ' . implode(';', $allowedHeaders)]);
        }
        // Если заголовок пришёл как строка — разбей её
        if (is_array($header) && count($header) === 1) {
            $header = explode(';', $header[0]);
        } elseif (!is_array($header)) {
            $header = explode(';', (string)$header);
        }
        
        // Теперь проверяем
        if ($header !== $allowedHeaders) {
            Log::error("Неверный заголовок CSV", ['expected' => $allowedHeaders, 'got' => $header]);
            return back()->with('import_errors', ['Неверный заголовок CSV. Должен быть: ' . implode(';', $allowedHeaders)]);
        }

        $errors = [];
        $successCount = 0;

        foreach ($rows as $index => $row) {
            if (count($row) < count($allowedHeaders)) {
                $errorMsg = "Строка " . ($index + 2) . ": недостаточно данных";
                Log::warning($errorMsg);
                $errors[] = $errorMsg;
                continue;
            }

            $data = array_combine($allowedHeaders, $row);

            try {
                Validator::make($data, [
                    'kl_id' => 'required|string|max:255',
                    'fio' => 'required|string|max:255',
                    'data_r' => 'nullable|date_format:d.m.Y',
                    'sex' => 'nullable|in:М,Ж',
                    'rip_at' => 'nullable|date_format:d.m.Y',
                    'created_rip' => 'nullable|date_format:d.m.Y H:i',
                    'komment' => 'nullable|string',
                ])->validate();

                // Преобразование дат
                $data['data_r'] = $data['data_r'] ? Carbon::createFromFormat('d.m.Y', $data['data_r'])->toDateString() : null;
                $data['rip_at'] = $data['rip_at'] ? Carbon::createFromFormat('d.m.Y', $data['rip_at'])->toDateString() : null;
                $data['created_rip'] = $data['created_rip'] ? Carbon::createFromFormat('d.m.Y H:i', $data['created_rip'])->toDateTimeString() : null;

                // Проверка дубликата
                if (FioDtrn::where('kl_id', $data['kl_id'])->exists()) {
                     $errorMsg = "Строка " . ($index + 2) . ": клиент с ID {$data['kl_id']} уже существует.";
                    Log::info($errorMsg);
                    $errors[] = $errorMsg;
                    continue;
                }

                // Сохранение клиента
                FioDtrn::create(array_merge($data, [
                    'user_id' => auth()->id(),
                ]));

                $successCount++;
                Log::info("Клиент импортирован", ['kl_id' => $data['kl_id'], 'fio' => $data['fio']]);
            } catch (ValidationException $e) { // Ловим ошибки валидации
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
}
