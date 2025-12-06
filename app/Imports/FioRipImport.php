<?php

namespace App\Imports;

use App\Models\FioRip;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Для логирования
use Illuminate\Support\Str; // Для работы со строками

class FioRipImport implements OnEachRow
{
    protected $userId;
    protected $importStats = [
        'total' => 0,
        'imported' => 0,
        'skipped' => 0,
        'skipped_reasons' => []
    ];

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function onRow(Row $row)
    {
        // Пропускаем строки до 7 (заголовки)
        if ($row->getIndex() < 7) {
            return;
        }

        $this->importStats['total']++;

        $rowArray = $row->toArray();

        // Проверяем наличие ФИО
        if (!isset($rowArray[1]) || empty(trim($rowArray[1]))) {
            $this->importStats['skipped']++;
            $reason = "отсутствует ФИО";
            $this->importStats['skipped_reasons'][] = "Строка {$row->getIndex()}: {$reason}";
            Log::info("Строка {$row->getIndex()} пропущена: {$reason}");
            return; // Пропускаем строку
        }

        // Получаем данные из строки
        $fio = trim($rowArray[1]); // ФИО
        $data_r = !empty($rowArray[2]) ? date('Y-m-d', strtotime($rowArray[2])) : null; // Дата рождения
        $sex = trim($rowArray[4]); // Пол

        // Проверка корректности пола
        if (!in_array($sex, ['М', 'Ж'])) {
            $this->importStats['skipped']++;
            $reason = "некорректное значение пола '{$sex}'";
            $this->importStats['skipped_reasons'][] = "Строка {$row->getIndex()}: {$reason}";
            Log::info("Строка {$row->getIndex()} пропущена: {$reason}");
            return; // Пропускаем строку
        }
        
        // Преобразование серии и номера паспорта
        $passportData = !empty($rowArray[10]) ? trim($rowArray[10]) : '';
        $kl_id = $this->convertPassportNumber($passportData);

        // Проверка, что kl_id не пустой и не равен null
        if (empty($kl_id)) {
            $this->importStats['skipped']++;
            $reason = "отсутствует или некорректный номер паспорта (kl_id)";
            $this->importStats['skipped_reasons'][] = "Строка {$row->getIndex()}: {$reason}";
            Log::info("Строка {$row->getIndex()} пропущена: {$reason}");
            return; // Пропускаем строку
        }

        // Проверка формата kl_id
        if (!$this->isValidKlIdFormat($kl_id)) {
            $this->importStats['skipped']++;
            $reason = "некорректный формат kl_id '{$kl_id}' (должен быть XXXX^XXXXXX)";
            $this->importStats['skipped_reasons'][] = "Строка {$row->getIndex()}: {$reason}";
            Log::info("Строка {$row->getIndex()} пропущена: {$reason}");
            return; // Пропускаем строку
        }


        // Дата смерти
        $rip_at = !empty($rowArray[7]) ? date('Y-m-d', strtotime($rowArray[7])) : null; // Столбец 8

        // Номер записи акта о смерти (столбец 9)
        $nom_zap = !empty($rowArray[8]) ? trim($rowArray[8]) : '';

        // Проверка уникальности nom_zap
        if (!empty($nom_zap)) {
            $existingRecord = FioRip::where('nom_zap', $nom_zap)->first();
            if ($existingRecord) {
                $this->importStats['skipped']++;
                $reason = "nom_zap '{$nom_zap}' уже существует";
                $this->importStats['skipped_reasons'][] = "Строка {$row->getIndex()}: {$reason}";
                Log::info("Строка {$row->getIndex()} пропущена: {$reason}");
                return; // Пропускаем строку
            }
        }

        // Проверка, что все необходимые поля заполнены для сохранения
        if (empty($fio)) {
            $this->importStats['skipped']++;
            $reason = "отсутствует ФИО после обработки";
            $this->importStats['skipped_reasons'][] = "Строка {$row->getIndex()}: {$reason}";
            Log::info("Строка {$row->getIndex()} пропущена: {$reason}");
            return;
        }

        // Создание новой записи
        try {
            FioRip::create([
                'kl_id' => $kl_id,
                'fio' => $fio,
                'data_r' => $data_r,
                'sex' => $sex,
                'adres' => !empty($rowArray[6]) ? trim($rowArray[6]) : null, // Адрес места жительства
                'rip_at' => $rip_at,
                'nom_zap' => $nom_zap,
                'user_id' => $this->userId, // ID текущего пользователя
                'komment' => null, // Комментарии не заполняем
            ]);
            $this->importStats['imported']++;
            Log::info("Строка {$row->getIndex()} успешно импортирована.");
        } catch (\Exception $e) {
            $this->importStats['skipped']++;
            $reason = "ошибка базы данных: " . $e->getMessage();
            $this->importStats['skipped_reasons'][] = "Строка {$row->getIndex()}: {$reason}";
            Log::error("Ошибка при импорте строки {$row->getIndex()}: " . $e->getMessage());
            // Можно выбрать: продолжать импорт или остановиться
            // Сейчас мы просто логируем и продолжаем
        }
    }

    /**
     * Возвращает статистику импорта
     * @return array
     */
    public function getStats()
    {
        return $this->importStats;
    }

    /**
     * Преобразует формат серии и номера паспорта из "36 15 176890" в "3615^176890".
     * @param string $passportString
     * @return string|null
     */
    private function convertPassportNumber($passportString)
    {
        if (empty($passportString)) {
            return null;
        }

        // Разбиваем строку по пробелам
        $parts = explode(' ', $passportString);
        if (count($parts) !== 3) {
            return null; // Неверный формат
        }

        // Извлекаем части
        [$series, $number, $code] = $parts;

        // Преобразуем в формат "серияномер^код"
        // Пример: "36 15 176890" -> "3615^176890"
        $kl_id = $series . $number . '^' . $code;

        return $kl_id;
    }
    
    /**
     * Проверяет формат kl_id в формате XXXX^XXXXXX
     * @param string $kl_id
     * @return bool
     */
    private function isValidKlIdFormat($kl_id)
    {
        // Проверяем, соответствует ли строка формату XXXX^XXXXXX
        // где X - цифры
        return (bool) preg_match('/^\d{4}\^\d{6}$/', $kl_id);
    }

}