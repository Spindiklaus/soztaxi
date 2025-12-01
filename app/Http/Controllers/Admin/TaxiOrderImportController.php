<?php
// app/Http/Controllers/Admin/TaxiOrderImportController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TaxiOrderImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaxiOrderImportController extends Controller
{
    protected $importService;

    public function __construct(TaxiOrderImportService $importService)
    {
        $this->importService = $importService;
    }

    public function showUploadForm()
    {
        return view('admin.taxi-orders.upload');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'original_file_path' => 'required|string', // Путь к оригинальному файлу
        ]);

        $uploadedFile = $request->file('file');
        $originalFilePath = $request->input('original_file_path');

        // Сохраняем загруженный файл
        $path = $uploadedFile->storeAs('imports', 'taxi_orders_imported_' . now()->format('Ymd_His') . '.' . $uploadedFile->getClientOriginalExtension());

        try {
            $changes = $this->importService->applyChanges(storage_path('app/' . $path), storage_path('app/' . $originalFilePath));

            return redirect()->back()->with('success', 'Изменения успешно применены.')->with('changes', $changes);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Ошибка при обработке файла: ' . $e->getMessage()]);
        }
    }
}