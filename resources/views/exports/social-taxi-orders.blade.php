<table>
    <thead>
        <tr>
            <th colspan="16" style="text-align: left; font-weight: bold; font-size: 14px; vertical-align: top;">
                Сведения по заказам с {{ $dateFrom }} по {{ $dateTo }}
            </th>
        </tr>
        <tr>
            <th colspan="16" style="text-align: left; font-weight: normal; font-size: 12px; vertical-align: top;">
                Сформировано: {{ $generatedAt->format('d.m.Y H:i') }}
            </th>
        </tr>
         <!-- строка для фильтров -->
        @if($filters && count($filters) > 0)
            <tr>
                <th colspan="16" style="text-align: left; font-weight: normal; font-size: 9px; background-color: #F0F0F0;">
                    Применённые фильтры: 
                    @foreach($filters as $filter)
                        {{ $filter }}{{ !$loop->last ? '; ' : '' }}
                    @endforeach
                </th>
            </tr>
        @endif
        
        
        
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center; white-space: normal; vertical-align: top;">№ п/п</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center; white-space: normal; vertical-align: top;">Тип поездки</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center; white-space: normal; vertical-align: top;">№ заказа</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center; white-space: normal; vertical-align: top;">Дата поездки</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center; white-space: normal; vertical-align: top;">Откуда</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center; white-space: normal; vertical-align: top;">Куда</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center; white-space: normal; vertical-align: top;">Обратно</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center; white-space: normal; vertical-align: top;">Дата обратно</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center; white-space: normal; vertical-align: top;">Скидка,%</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center; white-space: normal; vertical-align: top;">Дальность</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center; white-space: normal; vertical-align: top;">Цена за поездку</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center; white-space: normal; vertical-align: top;">Сумма к оплате</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center; white-space: normal; vertical-align: top;">Сумма к возмещению</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center; white-space: normal; vertical-align: top;">ФИО</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center; white-space: normal; vertical-align: top;">Категория инвалидности</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center; white-space: normal; vertical-align: top;">Доп. сведения</th>
        </tr>
    </thead>
    <tbody>
        @php $counter = 1; @endphp
        @foreach($orders as $order)
            @php
                $typeText = match ($order->type_order) {
                    1 => 'Соцтакси',
                    2 => 'Легковое авто',
                    3 => 'ГАЗель',
                    default => 'Неизвестно',
                };
                $visitDate = $order->visit_data ? \Carbon\Carbon::parse($order->visit_data)->format('d.m.Y') : '';
                $clientFio = $order->client ? $order->client->fio : '';
                $category = $order->category ? $order->category->name : '';
                $dopus = $order->dopus ? $order->dopus->name : '';
            @endphp
            <tr>
                <td>{{ $counter }}</td>
                <td>{{ $typeText }}</td>
                <td>{{ $order->pz_nom }}</td>
                <td>{{ $visitDate }}</td>
                <td>{{ $order->adres_kuda}}</td>
                <td>{{ $order->adres_otkuda}}</td>
                <td>{{ $order->adres_obratno }}</td>
                <td>{{ $order->visit_obratno ? $order->visit_obratno->format('d.m.Y H:i') : '' }}</td>
                <td>{{ $order->skidka_dop_all}}</td>
                <td>{{ $order->taxi_way}}</td> <!-- дальность -->
                <td>{{ $order->taxi_price}}</td> <!-- цена поездки -->
                <td>{{ $order->taxi_price - $order->taxi_vozm }}</td> <!-- к оплате -->
                <td>{{ $order->taxi_vozm}}</td>
                <td>{{ $clientFio }}</td>
                <td>{{ $category }}</td>
                <td>{{ $dopus }}</td>
            </tr>
            @php $counter++; @endphp
        @endforeach
    </tbody>
</table>