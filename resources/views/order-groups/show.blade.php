<!-- resources/views/order_groups/show.blade.php -->

<x-app-layout>
    <div class="container mx-auto py-8">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-800 leading-tight">Детали Группы: {{ $orderGroup->name }}</h2>
                <div class="mt-2">
                    <a href="{{ route('order-groups.index') }}" class="text-blue-600 hover:text-blue-900">&larr; Назад к списку</a>
                </div>
            </div>
            <div class="p-6">
                <div class="mb-6">
                    <p class="text-gray-700"><strong>ID:</strong> {{ $orderGroup->id }}</p>
                    <p class="text-gray-700"><strong>Название:</strong> {{ $orderGroup->name }}</p>
                    <p class="text-gray-700"><strong>Дата создания:</strong> {{ $orderGroup->created_at->format('d.m.Y H:i:s') }}</p>
                    <p class="text-gray-700"><strong>Дата обновления:</strong> {{ $orderGroup->updated_at->format('d.m.Y H:i:s') }}</p>
                </div>

                <h3 class="text-xl font-medium text-gray-800 mb-4">Заказы в группе</h3>
                @if($orderGroup->orders->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full table-auto divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Заказа</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Номер ПЗ</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Клиент</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Время поездки</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Откуда</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Куда</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($orderGroup->orders as $order)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $order->pz_nom }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->client ? $order->client->fio : 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->visit_data ? $order->visit_data->format('d.m.Y H:i') : 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->adres_otkuda }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->adres_kuda }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>    
                @else
                    <p class="text-gray-700">В этой группе пока нет заказов.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>