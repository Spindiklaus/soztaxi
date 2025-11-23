<table>
    <thead>
        <tr>
            <th colspan="17" style="text-align: center; font-weight: bold; font-size: 14px;">
                Сведения по заказам с {{ $dateFrom }} по {{ $dateTo }}
            </th>
        </tr>
        <tr>
            <th colspan="17" style="text-align: center; font-weight: bold; font-size: 12px;">
                Сформировано: {{ $generatedAt->format('d.m.Y H:i') }}
            </th>
        </tr>
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
            <th></th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center;">№ п/п</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center;">Тип поездки</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center;">№ заказа</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center;">Дата поездки</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center;">Откуда</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center;">Куда</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center;">Обратно</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center;">Дата обратно</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center;">Скидка,%</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center;">Дальность</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center;">Цена за поездку</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center;">Сумма к оплате</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center;">Сумма к возмещению</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center;">ФИО</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center;">Категория инвалидности</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center;">Доп. сведения</th>
            <th style="font-weight: bold; background-color: #E0E0E0; text-align: center;">Комментарии</th>
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
                $returnDate = $order->return_date ? \Carbon\Carbon::parse($order->return_date)->format('d.m.Y') : '';
                $clientFio = $order->client ? $order->client->fio : '';
                $category = $order->category ? $order->category->name : '';
                $dopus = $order->dopus ? $order->dopus->name : '';
            @endphp
            <tr>
                <td>{{ $counter }}</td>
                <td>{{ $typeText }}</td>
                <td>{{ $order->pz_nom }}</td>
                <td>{{ $visitDate }}</td>
                <td>{{ $order->from_address ?? '' }}</td>
                <td>{{ $order->to_address ?? '' }}</td>
                <td>{{ $order->is_return ? 'Да' : 'Нет' }}</td>
                <td>{{ $returnDate }}</td>
                <td>{{ $order->discount ?? 0 }}</td>
                <td>{{ $order->distance ?? 0 }}</td>
                <td>{{ $order->price ?? 0 }}</td>
                <td>{{ $order->payable_amount ?? 0 }}</td>
                <td>{{ $order->reimbursement_amount ?? 0 }}</td>
                <td>{{ $clientFio }}</td>
                <td>{{ $category }}</td>
                <td>{{ $dopus }}</td>
                <td>{{ $order->comment ?? '' }}</td>
            </tr>
            @php $counter++; @endphp
        @endforeach
    </tbody>
</table>