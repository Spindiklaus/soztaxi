<?php
// app/Services/OrderGroupingService.php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;

class OrderGroupingService
{
    private $timeToleranceMinutes = 30; // 30 минут
    private $addressTolerancePercent = 80; // 80% схожести адреса "куда"
    
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
                ];
                $processedOrderIds->push($order->id);
            }
        }
        

        return $potentialGroups;
    }

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