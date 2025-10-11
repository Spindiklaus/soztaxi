<?php
// app/Services/OrderGroupingService.php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;

class OrderGroupingService
{
    private $timeToleranceMinutes = 30; // 30 минут
    private $addressTolerancePercent = 80; // 80% схожести адреса "куда"
    
    // Геттер для времени толерантности
    public function getTimeToleranceMinutes(): int
    {
        return $this->timeToleranceMinutes;
    }

    public function findPotentialGroupsForDate($orders)
    {
        $potentialGroups = [];
        $processedOrderIds = collect(); // Коллекция ID уже обработанных заказов

        foreach ($orders as $order) {
            if ($processedOrderIds->contains($order->id)) {
                continue; // Пропускаем, если уже в группе
            }

            // Проверяем, можно ли добавить этот заказ в существующую группу
            $foundGroup = false;
            foreach ($potentialGroups as &$group) {
                if (count($group['orders']) < 4 && $this->isOrderCompatible($order, $group)) {
                    // Проверка на уникальность клиента в группе
                    $existingClientIds = collect($group['orders'])->pluck('client_id')->toArray();
                    if (!in_array($order->client_id, $existingClientIds)) {
                        $group['orders'][] = $order;
                        $processedOrderIds->push($order->id);
                        $foundGroup = true;
                        break; // Нашли подходящую группу, выходим из цикла по группам
                    }
                    // Если клиент уже в группе, ищем следующую группу
                }
            }

            // Если не нашли подходящую группу, создаем новую
            if (!$foundGroup) {
                $potentialGroups[] = [
                    'id' => uniqid('potential_group_'), // Временный ID для UI
                    'orders' => [$order],
                    'base_time' => $order->visit_data,
                    'base_to' => $order->adres_kuda, // Основной адрес "куда"
                    'name' => $this->generateGroupName([$order], $order->adres_kuda), // Генерируем имя сразу
                ];
                $processedOrderIds->push($order->id);
            }
        }
        
//        foreach ($potentialGroups as $idx => $grp) {
//        \Log::info("Group $idx after formation (before any post-processing):", [
//                'group_id' => $grp['id'],
//                'name' => $grp['name'], // Логируем имя
//                'order_ids' => collect($grp['orders'])->pluck('id')->toArray(),
//                'order_times' => collect($grp['orders'])->pluck('visit_data', 'id')->toArray(),
//        ]);
//}
        
        // Сортировка заказов ВНУТРИ каждой группы ПО ВРЕМЕНИ ---
        // хотя надо ли, пока не могу понять...
        foreach ($potentialGroups as &$group) {
            usort($group['orders'], function ($a, $b) {
                // Сравниваем время поездки
                return $a->visit_data <=> $b->visit_data;
            });
        }
        // --- КОНЕЦ Сортировкм ---
        
        
    // --- МИНИМАЛЬНЫЙ КОД: Отмета заказов с дублирующимся временем ВНУТРИ ГРУППЫ ---
        // Просто пройдемся по группам и установим флаги, не используя сложных ссылок
        $updatedGroups = [];
        foreach ($potentialGroups as $groupIndex => $groupData) { // $groupData - копия элемента
            $timeCounts = [];
            // Подсчитываем времена в копии
            foreach ($groupData['orders'] as $order) {
                $timeKey = $order->visit_data->format('Y-m-d H:i:s');
                $timeCounts[$timeKey] = ($timeCounts[$timeKey] ?? 0) + 1;
            }

            // Обновляем флаги, используя ссылки на исходные объекты Order в оригинальном $potentialGroups
            foreach ($potentialGroups[$groupIndex]['orders'] as $orderRef) {
                $orderTimeKey = $orderRef->visit_data->format('Y-m-d H:i:s');
                $isDuplicate = $timeCounts[$orderTimeKey] > 1;
                $orderRef->has_duplicate_time = $isDuplicate;
            }

            // Добавляем копию данных группы (без модификации структуры) в промежуточный массив
            // На самом деле, нам не нужно создавать $updatedGroups, мы модифицируем исходный массив через ссылки выше.
            // Просто убедимся, что структура не изменилась.
        }
        // --- КОНЕЦ МИНИМАЛЬНОГО КОДА ---
        
//        foreach ($potentialGroups as $idx => $grp) {
//        \Log::info("Group $idx after formation (before any post-processing):", [
//                'group_id' => $grp['id'],
//                'name' => $grp['name'], // Логируем имя
//                'order_ids' => collect($grp['orders'])->pluck('id')->toArray(),
//                'order_times' => collect($grp['orders'])->pluck('visit_data', 'id')->toArray(),
//        ]);
//}
        
        return $potentialGroups;
    }
    
    // --- НОВЫЙ МЕТОД: Генерация понятного имени для группы ---
    // на основе характеристик заказов в группе: количество человек, диапазон времени поездок, общий адрес "куда".
    private function generateGroupName(array $orders, string $commonDestination = null): string
    {
        if (empty($orders)) {
            return "Пустая группа";
        }

        $count = count($orders);

        // Определяем диапазон времени
        $times = collect($orders)->pluck('visit_data');
        $minTime = $times->min();
        $maxTime = $times->max();
        $timeRange = $minTime->format('H:i') . ' - ' . $maxTime->format('H:i');

        // Определяем общий адрес "куда" (если был передан, например, base_to)
        // Или используем адрес "куда" первого заказа, если base_to не был строго определен при создании
        $destination = $commonDestination ?: $orders[0]->adres_kuda;

        // Обрезаем адрес "куда" до первых 20 символов для краткости
        $shortDestination = strlen($destination) > 20 ? mb_substr($destination, 0, 20) . '...' : $destination;

        // Формируем имя
        return "Группа {$count} чел. | {$timeRange} | До: {$shortDestination}";
    }
    // --- КОНЕЦ НОВОГО МЕТОДА ---
    
    
    

    private function isOrderCompatible(Order $order, array $group): bool
    {
        // Проверка времени
        $timeDiff = abs($order->visit_data->diffInMinutes($group['base_time']));
        if ($timeDiff > $this->timeToleranceMinutes) {
            return false;
        }

        // Проверка адреса "куда"
        $addressToMatch = $this->addressesAreSimilar($order->adres_kuda, $group['base_to']);
        if (!$addressToMatch) {
            return false;
        }

        return true;
    }

    private function addressesAreSimilar(string $addr1, string $addr2): bool
    {
        $addr1 = trim(strtolower($addr1));
        $addr2 = trim(strtolower($addr2));

        // Проверка на полное совпадение
        if ($addr1 === $addr2) {
            return true;
        }

        // Проверка на частичное совпадение (например, содержит)
        if (str_contains($addr1, $addr2) || str_contains($addr2, $addr1)) {
            return true;
        }

        // Проверка схожести с использованием similar_text
        similar_text($addr1, $addr2, $percent);
        return $percent >= $this->addressTolerancePercent;
    }
}