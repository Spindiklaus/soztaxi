<?php

namespace App\Http\Controllers\Operator;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\FioDtrn;
use App\Models\Category;
use App\Models\Taxi;
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

        // Разрешённые заголовки (исправлено: nmv вместо category_id)
        $allowedHeaders = [
            'id', 'type_order', 'kl_id', 'client_tel', 'client_invalid', 'client_sopr',
            'nmv', 'category_skidka', 'category_limit', 'dopus_id', 'skidka_dop_all',
            'kol_p_limit', 'pz_nom', 'pz_data', 'adres_otkuda', 'adres_kuda', 'adres_obratno',
            'zena_type', 'visit_data', 'predv_way', 'taxi_id', 'taxi_sent_at', 'adres_trips_id',
            'taxi_price', 'taxi_way', 'taxi_vozm', 'otmena_data', 'otmena_taxi', 'closed_at',
            'komment', 'user_id', 'created_at', 'updated_at', 'deleted_at', 'visit_obratno'
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
        $statusHistoryBatch = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                if (count($row) < count($allowedHeaders)) {
                    $errors[] = "Строка " . ($index + 2) . ": недостаточно данных";
                    continue;
                }

                // Сопоставление данных
                $data = array_combine($allowedHeaders, $row);

                // Проверяем, существует ли уже заказ с таким ID
                if (!empty($data['id'])) {
                    // Используем withTrashed() для проверки, включая удаленные записи
                    if (Order::withTrashed()->where('id', $data['id'])->exists()) {
                        $errors[] = "Строка " . ($index + 2) . ": Заказ с ID={$data['id']} уже существует";
                        continue;
                    }
                }

                // Преобразование дат
                $data['pz_data'] = $this->convertDate($data['pz_data']);
                $data['visit_data'] = $this->convertDate($data['visit_data']);
                $data['taxi_sent_at'] = $this->convertDate($data['taxi_sent_at']);
                $data['otmena_data'] = $this->convertDate($data['otmena_data']);
                $data['closed_at'] = $this->convertDate($data['closed_at']);
                $data['created_at'] = $this->convertDate($data['created_at']);
                $data['updated_at'] = $this->convertDate($data['updated_at']);
                $data['visit_obratno'] = $this->convertDate($data['visit_obratno']);

                // Обработка deleted_at: 0 = не удален, 1 = удален (ставим дату)
                if ($data['deleted_at'] == '1') {
                    $data['deleted_at'] = Carbon::create(2025, 1, 1, 0, 0, 0);
                }
                else {
                    $data['deleted_at'] = null;
                }

                // Поиск client_id по kl_id с учетом разных форматов
                $client = $this->findClientByKlId($data['kl_id']);
                if (!$client) {
                    $errors[] = "Строка " . ($index + 2) . ": Не найден клиент с kl_id={$data['kl_id']}";
                    continue;
                }
                $data['client_id'] = $client->id;

                // Поиск category_id по NMV (в CSV nmv содержит NMV категории)
                if (!empty($data['nmv'])) {
                    $category = Category::where('nmv', $data['nmv'])->first();
                    if (!$category) {
                        $errors[] = "Строка " . ($index + 2) . ": Не найдена категория с NMV={$data['nmv']}";
                        continue;
                    }
                    $data['category_id'] = $category->id; // Заменяем NMV на ID категории
                }
                else {
                    $data['category_id'] = null;
                }

                // Проверка существования taxi_id
                if (!empty($data['taxi_id'])) {
                    if (!Taxi::where('id', $data['taxi_id'])->exists()) {
                        $errors[] = "Строка " . ($index + 2) . ": Не найден оператор такси с ID={$data['taxi_id']}";
                        continue;
                    }
                }

                // Очистка пустых значений
                foreach ($data as $key => $value) {
                    if ($value === '' || $value === '  -   -  : :' || $value === '-') {
                        $data[$key] = null;
                    }
                }

                // Подготавливаем данные для вставки (включая ID)
                $orderData = [
                    'id' => !empty($data['id']) ? (int) $data['id'] : null,
                    'type_order' => (int) $data['type_order'],
                    'client_id' => (int) $data['client_id'],
                    'client_tel' => $data['client_tel'],
                    'client_invalid' => $data['client_invalid'],
                    'client_sopr' => $data['client_sopr'],
                    'category_id' => !empty($data['category_id']) ? (int) $data['category_id'] : null,
                    'category_skidka' => $data['category_skidka'] ? (int) $data['category_skidka'] : null,
                    'category_limit' => $data['category_limit'] ? (int) $data['category_limit'] : null,
                    'dopus_id' => $data['dopus_id'] ? (int) $data['dopus_id'] : null,
                    'skidka_dop_all' => $data['skidka_dop_all'] ? (int) $data['skidka_dop_all'] : null,
                    'kol_p_limit' => $data['kol_p_limit'] ? (int) $data['kol_p_limit'] : null,
                    'pz_nom' => $data['pz_nom'],
                    'pz_data' => $data['pz_data'],
                    'adres_otkuda' => $data['adres_otkuda'],
                    'adres_kuda' => $data['adres_kuda'],
                    'adres_obratno' => $data['adres_obratno'],
                    'zena_type' => (int) $data['zena_type'],
                    'visit_data' => $data['visit_data'],
                    'predv_way' => $data['predv_way'] ? (float) $data['predv_way'] : null,
                    'taxi_id' => !empty($data['taxi_id']) ? (int) $data['taxi_id'] : null,
                    'taxi_sent_at' => $data['taxi_sent_at'],
                    'taxi_price' => $data['taxi_price'] ? (float) $data['taxi_price'] : null,
                    'taxi_way' => $data['taxi_way'] ? (float) $data['taxi_way'] : null,
                    'taxi_vozm' => $data['taxi_vozm'] ? (float) $data['taxi_vozm'] : null,
                    'cancelled_at' => $data['otmena_data'],
                    'otmena_taxi' => (int) $data['otmena_taxi'],
                    'closed_at' => $data['closed_at'],
                    'komment' => $data['komment'],
                    'user_id' => (int) $data['user_id'],
                    'created_at' => $data['created_at'],
                    'updated_at' => $data['updated_at'],
                    'deleted_at' => $data['deleted_at'] ?? null,
                    'visit_obratno' => $data['visit_obratno'] ?? null
                ];

                // Убираем null ID, если он пустой
                if (empty($orderData['id'])) {
                    unset($orderData['id']);
                }

                // Добавляем данные в пакет
                $batch[] = $orderData;

                // Определяем начальный статус для этого заказа
                $statusId = $this->determineInitialStatus((object) $data);
                $userId = auth()->id() ?? (int) $data['user_id'] ?? 1;

                // Сохраняем информацию о статусе для последующей вставки
                $statusHistoryBatch[] = [
                    'status_order_id' => $statusId,
                    'user_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Если пакет заполнен, сохраняем его
                if (count($batch) === $batchSize) {
                    // Вставляем заказы
                    $insertedOrders = $this->insertOrdersWithIds($batch);
                    $successCount += count($insertedOrders);

                    // Устанавливаем статусы для вставленных заказов
                    $this->setOrderStatuses($insertedOrders, $statusHistoryBatch);

                    $batch = [];
                    $statusHistoryBatch = [];
                }
            }

            // Сохраняем оставшиеся записи
            if (!empty($batch)) {
                $insertedOrders = $this->insertOrdersWithIds($batch);
                $successCount += count($insertedOrders);

                // Устанавливаем статусы для вставленных заказов
                $this->setOrderStatuses($insertedOrders, $statusHistoryBatch);
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

    /**
     * Вставка заказов с сохранением ID
     */
    private function insertOrdersWithIds($batch) {
        if (empty($batch)) {
            return [];
        }

        try {
            // Разделяем заказы с ID и без ID
            $withId = array_filter($batch, function ($order) {
                return isset($order['id']);
            });
            $withoutId = array_filter($batch, function ($order) {
                return !isset($order['id']);
            });

            $insertedOrders = [];

            if (!empty($withId)) {
                foreach ($withId as $order) {
                    // Проверяем, существует ли запись
                    if (isset($order['id'])) {
                        $existingOrder = Order::withTrashed()->where('id', $order['id'])->first();
                        if ($existingOrder) {
                            // Обновляем существующую запись
                            Order::withTrashed()->where('id', $order['id'])->update($order);
                            $insertedOrders[] = ['id' => $order['id'], 'data' => $order];
                        }
                        else {
                            // Создаем новую запись
                            $orderId = DB::table('orders')->insertGetId($order);
                            $insertedOrders[] = ['id' => $orderId, 'data' => $order];
                        }
                    }
                }
            }

            // Вставляем заказы без ID (автоинкремент)
            if (!empty($withoutId)) {
                foreach ($withoutId as $order) {
                    $orderId = DB::table('orders')->insertGetId($order);
                    $insertedOrders[] = ['id' => $orderId, 'data' => $order];
                }
            }

            return $insertedOrders;
        } catch (\Exception $e) {
            // Если возникает ошибка дубликата, выбрасываем исключение
            throw $e;
        }
    }

    /**
     * Установка статусов для вставленных заказов
     */
    private function setOrderStatuses($insertedOrders, $statusHistoryBatch) {
        if (empty($insertedOrders) || empty($statusHistoryBatch)) {
            return;
        }

        $statusHistoryData = [];
        foreach ($insertedOrders as $index => $orderInfo) {
            if (isset($statusHistoryBatch[$index])) {
                $statusHistoryData[] = array_merge(
                        $statusHistoryBatch[$index],
                        ['order_id' => $orderInfo['id']]
                );
            }
        }

        if (!empty($statusHistoryData)) {
            DB::table('order_status_histories')->insert($statusHistoryData);
        }
    }

    /**
     * Определяем начальный статус на основе данных заказа
     */
    private function determineInitialStatus($orderData) {
        // Логика определения статуса:
        if (!empty($orderData->closed_at)) {
            return 4; // Закрыт
        }
        elseif (!empty($orderData->otmena_data) || !empty($orderData->cancelled_at)) {
            return 3; // Отменён
        }
        elseif (!empty($orderData->taxi_sent_at)) {
            return 2; // Передан в такси
        }
        else {
            return 1; // Принят (по умолчанию)
        }
    }

    /**
     * Поиск клиента по kl_id с учетом разных форматов
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
        }
        else {
            // Если нет пробела, добавляем его после 2-го символа
            if (strlen($klId) >= 4 && $klId[2] !== '^') {
                $alternateKlId = substr($klId, 0, 2) . ' ' . substr($klId, 2);
                $client = FioDtrn::where('kl_id', $alternateKlId)->first();
            }
        }

        return $client;
    }

   private function convertDate($dateString) {
    // Проверяем пустые значения
    if (empty($dateString) || 
        $dateString === '  -   -  : :' || 
        $dateString === '-' || 
        $dateString === '0' ||
        $dateString === ':' ||
        trim($dateString) === '' ||
        trim($dateString) === '-   -  : :') {
        return null;
    }
    
    // Убираем пробелы по краям и специальные символы
    $dateString = trim($dateString);
    
    // Отладка - посмотрим, что приходит
    \Log::info('convertDate debug', ['input' => $dateString, 'length' => strlen($dateString)]);
    
    // Формат даты в CSV: дд.мм.гг чч:мм или дд.мм.гггг чч:мм
    // Используем более гибкое регулярное выражение
    if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{2,4})\s+(\d{1,2}):(\d{2})/', $dateString, $matches)) {
        $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        $year = strlen($matches[3]) == 2 ? '20' . $matches[3] : $matches[3];
        $hour = str_pad($matches[4], 2, '0', STR_PAD_LEFT);
        $minute = $matches[5];
        
        $result = "$year-$month-$day $hour:$minute:00";
        \Log::info('convertDate success', ['input' => $dateString, 'output' => $result]);
        return $result;
    }
    
    // Если формат не распознан, возвращаем null
    \Log::warning('Не удалось распознать формат даты', ['dateString' => $dateString]);
    return null;
}
}
