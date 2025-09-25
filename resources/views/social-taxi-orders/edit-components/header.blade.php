<!-- resources/views/social-taxi-orders/edit-components/header.blade.php -->
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">
        Редактирование заказа: 
        <span class="{{ getOrderTypeColor($order->type_order) }} font-medium">
            {{ getOrderTypeName($order->type_order) }}
        </span>
    </h1>

    @php
    $backToOperator = request('back_to_operator');
    $operatorType = request('operator_type');

    if ($backToOperator && $operatorType) {
    $backRoute = route($backToOperator, array_merge(['type_order' => $operatorType], $backUrlParams ?? []));
    } else {
    $backRoute = route('social-taxi-orders.index', $backUrlParams ?? []);
    }
    @endphp

    <a href="{{ $backRoute }}" 
       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
        Назад к списку
    </a>
</div>