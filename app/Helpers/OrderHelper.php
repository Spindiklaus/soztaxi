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
        // Используем withTrashed() чтобы учитывать удаленные заказы
        $maxOrder = \App\Models\Order::withTrashed()
            ->where('user_id', $userId)
            ->where('pz_nom', 'like', $prefix . '-______' . $litera)
            ->orderBy('pz_nom', 'desc')
            ->first();
    
        $maxNumber = 0;
        if ($maxOrder) {
            // Извлекаем номер из pz_nom
            if (preg_match('/^' . $prefix . '-(\d+)' . $litera . '$/', $maxOrder->pz_nom, $matches)) {
                $maxNumber = (int)$matches[1];
            }
        }
    
        $nextNumber = $maxNumber + 1;

    
        // Формируем номер заказа: Соц-000499SEM, ЛАВ-000001SEM, ГАЗ-000001SEM
        return $prefix . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT) . $litera;
    }
}

if (!function_exists('getRussianMonthName')) {
    /**
     * Получить русское название месяца в родительном падеже по дате
     *
     * @param string|\Carbon\Carbon|\DateTime|null $date Дата
     * @return string Русское название месяца (например, 'января', 'февраля', ...)
     */
    function getRussianMonthName($date = null)
    {
        $months = [
            1 => 'Январь',
            2 => 'Февраль',
            3 => 'Март',
            4 => 'Апрель',
            5 => 'Май',
            6 => 'Июнь',
            7 => 'Июль',
            8 => 'Август',
            9 => 'Сентябрь',
            10 => 'Октябрь',
            11 => 'Ноябрь',
            12 => 'Декабрь',
        ];

        if ($date === null) {
            $date = now(); // Используем текущую дату, если не передана
        }

        $monthNumber = (int) $date->format('n'); // 'n' - месяц без ведущего нуля (1-12)

        return $months[$monthNumber];
    }
}
