<?php

namespace App\Http\Controllers\Operator;

use Illuminate\Http\Request;
use App\Models\FioDtrn;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

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

        $rows = array_map('str_getcsv', file($path));
        $header = array_shift($rows); // Убираем заголовок

        $allowedHeaders = ['kl_id', 'fio', 'data_r', 'sex', 'rip_at', 'created_rip', 'komment'];

        if ($header !== $allowedHeaders) {
            return back()->with('error', 'Неверный формат CSV. Заголовки должны быть: ' . implode(';', $allowedHeaders));
        }

        $errors = [];
        $successCount = 0;

        foreach ($rows as $index => $row) {
            if (count($row) < count($allowedHeaders)) {
                $errors[] = "Строка " . ($index + 2) . ": недостаточно данных";
                continue;
            }

            $data = array_combine($allowedHeaders, $row);

            try {
                Validator::make($data, [
                    'kl_id' => 'required|string|max:255',
                    'fio' => 'required|string|max:255',
                    'data_r' => 'nullable|date_format:d.m.Y',
                    'sex' => 'nullable|in:M,F',
                    'rip_at' => 'nullable|date_format:d.m.Y H:i',
                    'created_rip' => 'nullable|date_format:d.m.Y H:i',
                    'komment' => 'nullable|string',
                ])->validate();

                // Преобразование дат
                $data['data_r'] = $data['data_r'] ? Carbon::createFromFormat('d.m.Y', $data['data_r'])->toDateString() : null;
                $data['rip_at'] = $data['rip_at'] ? Carbon::createFromFormat('d.m.Y H:i', $data['rip_at'])->toDateTimeString() : null;
                $data['created_rip'] = $data['created_rip'] ? Carbon::createFromFormat('d.m.Y H:i', $data['created_rip'])->toDateTimeString() : null;

                // Проверка дубликата
                if (FioDtrn::where('kl_id', $data['kl_id'])->exists()) {
                    $errors[] = "Строка " . ($index + 2) . ": клиент с ID {$data['kl_id']} уже существует.";
                    continue;
                }

                // Сохранение клиента
                FioDtrn::create(array_merge($data, [
                    'user_id' => auth()->id(),
                ]));

                $successCount++;
            } catch (ValidationException $e) {
                $errors[] = "Строка " . ($index + 2) . ": " . collect($e->validator->errors()->all())->join(', ');
            }
        }

        if (!empty($errors)) {
            return back()
                ->with('import_errors', $errors)
                ->with('success_count', $successCount);
        }

        return redirect()->route('fiodtrns.index')
            ->with('success', "Успешно импортировано: {$successCount} клиент(ов)");
    }
}
