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

if (!function_exists('generateOrderNumber')) {
    /**
     * Генерация номера заказа по типу
     *
     * @param int $type Тип заказа
     * @param int|null $userId ID пользователя (оператора)
     * @return string Номер заказа
     */
    function generateOrderNumber($type, $userId = null) 
    {
        // Если ID пользователя не указан, берем текущего авторизованного пользователя
        if (!$userId && auth()->check()) {
            $userId = auth()->id();
        }
        
        // Получаем литеру оператора
        $litera = 'UNK'; // По умолчанию
        if ($userId) {
            $user = \App\Models\User::find($userId);
            $litera = $user && $user->litera ? strtoupper($user->litera) : 'UNK';
        }
        
        // Определяем префикс по типу заказа
        $prefixes = [
            1 => 'Соц',  // Соцтакси
            2 => 'ЛАВ',  // Легковое авто
            3 => 'ГАЗ',  // ГАЗель
        ];
        
        $prefix = $prefixes[$type] ?? 'UNK';
        
        // Получаем максимальный номер заказа для этого оператора и префикса
    $maxNumber = 0;
    $lastOrders = \App\Models\Order::where('user_id', $userId)
        ->where('pz_nom', 'like', $prefix . '-______' . $litera)
        ->get();
    
     foreach ($lastOrders as $order) {
        // Извлекаем номер из pz_nom
        if (preg_match('/^' . $prefix . '-(\d+)' . $litera . '$/', $order->pz_nom, $matches)) {
            $number = (int)$matches[1];
            if ($number > $maxNumber) {
                $maxNumber = $number;
            }
        }
    }
    $nextNumber = $maxNumber + 1;
    
        // Формируем номер заказа: ST-000499SEM, LA-000001SEM, GA-000001SEM
        return $prefix . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT) . $litera;
    }
}