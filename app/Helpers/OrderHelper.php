<?php

// тип поездки

if (!function_exists('getOrderTypeName')) {
    /**
     * Получить название типа заказа
     *
     * @param int $typeId
     * @return string
     */
    function getOrderTypeName($typeId)
    {
        $types = [
            1 => 'Соцтакси',
            2 => 'Легковое авто',
            3 => 'ГАЗель'
        ];
        
        return $types[$typeId] ?? 'Неизвестный тип';
    }
}

if (!function_exists('getOrderTypeColor')) {
    /**
     * Получить цвет типа заказа
     *
     * @param int $typeId
     * @return string
     */
    function getOrderTypeColor($typeId)
    {
        $colors = [
            1 => 'text-blue-600',
            2 => 'text-green-600',
            3 => 'text-yellow-600'
        ];
        
        return $colors[$typeId] ?? 'text-gray-600';
    }
}

if (!function_exists('getOrderTypeBadgeColor')) {
    /**
     * Получить цвет бейджа типа заказа
     *
     * @param int $typeId
     * @return string
     */
    function getOrderTypeBadgeColor($typeId)
    {
        $colors = [
            1 => 'bg-blue-100 text-blue-800',
            2 => 'bg-green-100 text-green-800',
            3 => 'bg-yellow-100 text-yellow-800'
        ];
        
        return $colors[$typeId] ?? 'bg-gray-100 text-gray-800';
    }
}