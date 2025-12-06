<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\FioRip;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\FioRipImport; // Импорт для Excel
use Illuminate\Support\Facades\Auth; // Для получения текущего пользователя
use Illuminate\Support\Facades\Validator; // Для валидации
use Illuminate\Support\Facades\Log;

class FioRipImportController extends BaseController
{
    public function showForm()
    {
        return view('fio_rips.importRIP'); // Создайте этот view
    }

    public function import(Request $request)
    {
        // Валидация загруженного файла
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $file = $request->file('file');

        // Получаем ID текущего пользователя
        $userId = Auth::id(); // Предполагается, что пользователь залогинен

        // Используем импорт с обработкой ошибок
        try {
            $import = new FioRipImport($userId);
            Excel::import($import, $file); // Передаем ID пользователя в импорт
            
            // Получаем статистику
            $stats = $import->getStats();
            
            // Логируем статистику
            Log::info("Импорт завершен. Всего строк: {$stats['total']}, Импортировано: {$stats['imported']}, Пропущено: {$stats['skipped']}");
            
            // Если есть причины пропуска, логируем их
            if (!empty($stats['skipped_reasons'])) {
                foreach ($stats['skipped_reasons'] as $reason) {
                    Log::info($reason);
                }
            }
            
            // Передаем статистику в сессию для отображения пользователю
            session()->flash('import_stats', $stats);
            session()->flash('import_skipped_reasons', $stats['skipped_reasons']);
            
            return redirect()->route('fio_rips.index')->with('success', 'Данные успешно импортированы.');
        } catch (\Exception $e) {
            // Обработка ошибок (например, ошибка формата файла)
            \Log::error("Ошибка импорта FIO_RIP: " . $e->getMessage());
            return redirect()->back()->with('error', 'Произошла ошибка при импорте: ' . $e->getMessage());
        }
    }
}