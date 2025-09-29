<!-- шаблон экспорта -->
<table>
    <tr>
        <td><strong>Сведения для передачи оператору такси с {{ $visitDateFrom }} по {{ $visitDateTo }}</strong></td>
    </tr>
    <tr>
        <td>Сформировано: {{ $generatedAt->format('d.m.Y H:i') }}</td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td><strong>№ п/п</strong></td>
        <td><strong>Тип поездки</strong></td>
        <td><strong>№ заказа</strong></td>
        <td><strong>Дата поездки</strong></td>
        <td><strong>Откуда</strong></td>
        <td><strong>Куда</strong></td>
        <td><strong>Обратно</strong></td>
        <td><strong>Дата обратно</strong></td>
        <td><strong>Сотовый</strong></td>
        <td><strong>Скидка, %</strong></td>
        <td><strong>Предв. дальность</strong></td>
        <td><strong>Цена за поездку</strong></td>
        <td><strong>Сумма к оплате</strong></td>
        <td><strong>Сумма к возмещению</strong></td>
        <td><strong>Категория инвалидности</strong></td>
        <td><strong>Доп. сведения</strong></td>
    </tr>
    @foreach($orders as $index => $order)
    <tr>
        <td>{{ $index + 1 }}</td>
        <td>{{ getOrderTypeName($order->type_order) }}</td>
        <td>{{ $order->pz_nom }}</td>
        <td>{{ $order->visit_data ? $order->visit_data->format('d.m.Y H:i') : '-' }}</td>
        <td>{{ $order->adres_otkuda ?? '' }}</td>
        <td>{{ $order->adres_kuda ?? '' }}</td>
        <td>{{ $order->adres_obratno ?? '' }}</td>
        <td>{{ $order->visit_obratno ? $order->visit_obratno->format('d.m.Y H:i') : '' }}</td>
        <td>{{ $order->client_tel ?? '' }} </td>
        <td>{{ $order->skidka_dop_all ?? '' }}</td>
        <td>{{ $order->predv_way ?? '' }}</td>
        <!-- Цена за поездку -->
        <td>
            @if($order->type_order == 1) <!-- для соцтакси предварительная -->
                {{ number_format(calculateFullTripPrice($order, 11, $taxi), 11, ',', ' ') }}
            @else
                {{ $order->taxi_price ?? '' }}
            @endif
        </td>
        
        <!-- Сумма к оплате -->
        <td>
            @if($order->type_order == 1) <!-- для соцтакси предварительная -->
                {{ number_format(calculateClientPaymentAmount($order, 11, $taxi), 11, ',', ' ') }}
            @endif
        </td>
        
        <!-- Сумма к возмещению -->
        <td>
            @if($order->type_order == 1) <!-- для соцтакси предварительная к возмещению-->
                {{ number_format(calculateReimbursementAmount($order, 11, $taxi), 11, ',', ' ') }}
            @else
                {{ $order->taxi_vozm ?? '' }}
            @endif
        </td>
        <td>{{ $order->category ? $order->category->name : '' }}</td>
        <td>{{ $order->dopus ? $order->dopus->name : '' }}</td>
    </tr>
    @endforeach
</table>