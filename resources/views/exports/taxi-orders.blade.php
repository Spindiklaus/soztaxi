<!-- шаблон экспорта старый, со старой группировкой-->
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
        <!--<td><strong>Тип поездки</strong></td>-->
        <td><strong>№ заказа</strong></td>
        <td><strong>Дата поездки</strong></td>
        <td><strong>Откуда</strong></td>
        <td><strong>Куда</strong></td>
        <!--<td><strong>Обратно</strong></td>-->
        <!--<td><strong>Дата обратно</strong></td>-->
        <td><strong>Сотовый</strong></td>
        <td><strong>Скидка, %</strong></td>
        <td><strong>Предв. дальность</strong></td>
        <td><strong>Цена за поездку</strong></td>
        <td><strong>Сумма к оплате</strong></td>
        <td><strong>Сумма к возмещению</strong></td>
        <td><strong>Категория инвалидности</strong></td>
        <td><strong>Доп. сведения</strong></td>
        <!--<td><strong>Группировка</strong></td>-->
    </tr>
    
    @php
        $currentGroup = null;
        $rowNumber = 1;
    @endphp
    
    @foreach($orders as $order)
            <!-- Это первый заказ в группе (или одиночный) -->
            <tr>
                <td>{{ $rowNumber }}</td> <!-- Номер п/п увеличивается только здесь -->
<!--                <td>{{-- getOrderTypeName($order->type_order) --}}</td>-->
                <td>{{ $order->pz_nom }}</td>
                <td>{{ $order->visit_data->format('d.m.Y H:i')}}</td>
                <td>{{ $order->adres_otkuda }} {{ $order->adres_otkuda_info }}</td>
                <td>{{ $order->adres_kuda }} {{ $order->adres_kuda_info }}</td>
                <td>{{ $order->client_tel ?? '' }} </td>
                <td>{{ $order->skidka_dop_all ?? '' }}</td>
                <td>{{ $order->predv_way ?? '' }}</td>
                <!-- Цена за поездку -->
                <td>
                    {{ number_format(calculateFullTripPrice($order, 11, $taxi), 11, ',', ' ') }}
                </td>
                <!-- Сумма к оплате -->
                <td>
                    {{ number_format(calculateClientPaymentAmount($order, 11, $taxi), 11, ',', ' ') }}
                </td>
                <!-- Сумма к возмещению -->
                <td>
                    {{ number_format(calculateReimbursementAmount($order, 11, $taxi), 11, ',', ' ') }}
                </td>
                <td>{{ $order->category ? $order->category->name : '' }}</td>
                <td>{{ $order->dopus ? $order->dopus->name : '' }}</td>
            </tr>
        @php
            $rowNumber++
        @endphp    
    @endforeach
</table>