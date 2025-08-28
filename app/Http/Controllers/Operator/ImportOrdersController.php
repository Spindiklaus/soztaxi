<?php

namespace App\Http\Controllers\Operator;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\FioDtrn;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportOrdersController extends BaseController {

    // Показываем форму импорта
    public function showImportForm() {
        return view('social-taxi-orders.import');
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
        $allowedHeaders = [
            'id', 'type_order', 'kl_id', 'client_tel', 'client_invalid', 'client_sopr', 
            'category_id', 'category_skidka', 'category_limit', 'dopus_id', 'skidka_dop_all', 
            'kol_p_limit', 'pz_nom', 'pz_data', 'adres_otkuda', 'adres_kuda', 'adres_obratno', 
            'zena_type', 'visit_data', 'predv_way', 'taxi_id', 'taxi_data', 'adres_trips_id', 
            'taxi_sent_at', 'taxi_price', 'taxi_way', 'otmena_data', 'otmena_taxi', 'closed_at', 
            'komment', 'user_id', 'created_at', 'updated_at', 'deleted_at'
        ];

        if ($header !== $allowedHeaders) {
            Log::error("Неверный заголовок CSV", ['expected' => $allowedHeaders, 'got' => $header]);
            return back()->with('import_errors', ['Неверный заголовок CSV.']);
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

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                if (count($row) < count($allowedHeaders)) {
                    $errors[] = "Строка " . ($index + 2) . ": недостаточно данных";
                    continue;
                }

                // Сопоставление данных
                $data = array_combine($allowedHeaders, $row);

                // Преобразование дат
                $data['pz_data'] = $this->convertDate($data['pz_data']);
                $data['visit_data'] = $this->convertDate($data['visit_data']);
                $data['taxi_data'] = $this->convertDate($data['taxi_data']);
                $data['taxi_sent_at'] = $this->convertDate($data['taxi_sent_at']);
                $data['otmena_data'] = $this->convertDate($data['otmena_data']);
                $data['closed_at'] = $this->convertDate($data['closed_at']);
                $data['created_at'] = $this->convertDate($data['created_at']);
                $data['updated_at'] = $this->convertDate($data['updated_at']);
                
                // Обработка deleted_at: 0 = не удален, 1 = удален (ставим дату)
                if ($data['deleted_at'] == '1') {
                    $data['deleted_at'] = Carbon::create(2025, 1, 1, 0, 0, 0);
                } else {
                    $data['deleted_at'] = null;
                }

                // Поиск client_id по kl_id с учетом разных форматов (3602 или 36 02)
                $client = $this->findClientByKlId($data['kl_id']);
                if (!$client) {
                    $errors[] = "Строка " . ($index + 2) . ": Не найден клиент с kl_id={$data['kl_id']}";
                    continue;
                }
                $data['client_id'] = $client->id;

                // Очистка пустых значений
                foreach ($data as $key => $value) {
                    if ($value === '' || $value === '  -   -  : :' || $value === '-') {
                        $data[$key] = null;
                    }
                }

                // Добавляем данные в пакет
                $batch[] = [
                    'type_order' => (int)$data['type_order'],
                    'client_id' => (int)$data['client_id'],
                    'client_tel' => $data['client_tel'],
                    'client_invalid' => $data['client_invalid'],
                    'client_sopr' => $data['client_sopr'],
                    'category_id' => (int)$data['category_id'],
                    'category_skidka' => (int)$data['category_skidka'],
                    'category_limit' => (int)$data['category_limit'],
                    'dopus_id' => $data['dopus_id'] ? (int)$data['dopus_id'] : null,
                    'skidka_dop_all' => $data['skidka_dop_all'] ? (int)$data['skidka_dop_all'] : null,
                    'kol_p_limit' => $data['kol_p_limit'] ? (int)$data['kol_p_limit'] : null,
                    'pz_nom' => $data['pz_nom'],
                    'pz_data' => $data['pz_data'],
                    'adres_otkuda' => $data['adres_otkuda'],
                    'adres_kuda' => $data['adres_kuda'],
                    'adres_obratno' => $data['adres_obratno'],
                    'zena_type' => (int)$data['zena_type'],
                    'visit_data' => $data['visit_data'],
                    'predv_way' => $data['predv_way'] ? (float)$data['predv_way'] : null,
                    'taxi_id' => (int)$data['taxi_id'],
                    'taxi_sent_at' => $data['taxi_sent_at'],
                    'taxi_price' => $data['taxi_price'] ? (float)$data['taxi_price'] : null,
                    'taxi_way' => $data['taxi_way'] ? (float)$data['taxi_way'] : null,
                    'cancelled_at' => $data['otmena_data'],
                    'otmena_taxi' => (int)$data['otmena_taxi'],
                    'closed_at' => $data['closed_at'],
                    'komment' => $data['komment'],
                    'user_id' => (int)$data['user_id'],
                    'created_at' => $data['created_at'],
                    'updated_at' => $data['updated_at'],
                    'deleted_at' => $data['deleted_at'],
                ];

                // Если пакет заполнен, сохраняем его
                if (count($batch) === $batchSize) {
                    Order::insert($batch);
                    $batch = [];
                    $successCount += $batchSize;
                }
            }

            // Сохраняем оставшиеся записи
            if (!empty($batch)) {
                Order::insert($batch);
                $successCount += count($batch);
            }

            DB::commit();

            if (!empty($errors)) {
                return back()
                                ->with('import_errors', $errors)
                                ->with('success_count', $successCount);
            }

            return redirect()->route('social-taxi-orders.index')
                            ->with('success', "Успешно импортировано: {$successCount} записей");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Ошибка при импорте", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('import_errors', ['Ошибка при импорте: ' . $e->getMessage()]);
        }
    }

    private function convertDate($dateString) {
        if (empty($dateString) || $dateString === '  -   -  : :' || $dateString === '-' || $dateString === '0') {
            return null;
        }
        
        // Формат даты в CSV: дд.мм.гг чч:мм или дд.мм.гггг чч:мм
        if (preg_match('/^(\d{2})\.(\d{2})\.(\d{2,4})\s+(\d{2}):(\d{2})$/', $dateString, $matches)) {
            $day = $matches[1];
            $month = $matches[2];
            $year = strlen($matches[3]) == 2 ? '20' . $matches[3] : $matches[3];
            $hour = $matches[4];
            $minute = $matches[5];
            return "$year-$month-$day $hour:$minute:00";
        }
        
        return null;
    }
    
    /**
     * Поиск клиента по kl_id с учетом разных форматов
     * Проверяет оба формата: "36 04^123456" и "3604^123456"
     */
    private function findClientByKlId($klId) {
        // Сначала ищем по оригинальному формату
        $client = FioDtrn::where('kl_id', $klId)->first();
        if ($client) {
            return $client;
        }

        // Если не найден, пробуем другой формат
        if (strpos($klId, ' ') !== false) {
            // Если есть пробел, убираем его
            $alternateKlId = str_replace(' ', '', $klId);
            $client = FioDtrn::where('kl_id', $alternateKlId)->first();
        } else {
            // Если нет пробела, добавляем его после 2-го символа
            if (strlen($klId) >= 4 && $klId[2] !== '^') {
                $alternateKlId = substr($klId, 0, 2) . ' ' . substr($klId, 2);
                $client = FioDtrn::where('kl_id', $alternateKlId)->first();
            }
        }

        return $client;
    }

}