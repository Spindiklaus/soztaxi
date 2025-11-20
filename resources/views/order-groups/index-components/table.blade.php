<!-- resources/views/order-groups/index-components/table.blade.php -->
<!-- Обертка для прокрутки таблицы -->
    <table class="min-w-full divide-y divide-gray-200 bg-white">
        <thead class="bg-gray-50 text-gray-200 sticky top-0 z-10 shadow-lg">
            <tr>
                <th scope="col" class="px-6 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <!-- Сортировка по дате поездки -->
                    <button onclick="sortBy('visit_date')" class="focus:outline-none">
                        Дата поездки
                        @if(request('sort') === 'visit_date')
                        <span class="ml-1">{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-6 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <!-- Сортировка по названию -->
                    <button onclick="sortBy('name')" class="focus:outline-none">
                        Название группы (условное)
                        @if(request('sort') === 'name')
                        <span class="ml-1">{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-6 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <!-- Сортировка по количеству заказов -->
                    <button onclick="sortBy('orders_count')" class="focus:outline-none">
                        Кол-во заказов в поездке
                        @if(request('sort') === 'orders_count')
                        <span class="ml-1">{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </button>
                </th>
<!--                <th scope="col" class="px-6 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                     Сортировка по километражу 
                    <button onclick="sortBy('taxi_way')" class="focus:outline-none">
                        Километраж
                        @if(request('sort') === 'taxi_way')
                        <span class="ml-1">{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-6 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                     Сортировка по цене 
                    <button onclick="sortBy('taxi_price')" class="focus:outline-none">
                        Цена поездки
                        @if(request('sort') === 'taxi_price')
                        <span class="ml-1">{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-6 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                     Сортировка по сумме возмещения 
                    <button onclick="sortBy('taxi_vozm')" class="focus:outline-none">
                        Сумма к возмещению
                        @if(request('sort') === 'taxi_vozm')
                        <span class="ml-1">{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </button>
                </th>-->
                <th scope="col" class="px-6 py-1 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Действия
                </th>
                <th scope="col" class="px-6 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <!-- Сортировка по дате создания -->
                    <button onclick="sortBy('created_at')" class="focus:outline-none">
                        Создано
                        @if(request('sort') === 'created_at')
                        <span class="ml-1">{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </button>
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($orderGroups as $group)
            <tr>
                <td class="px-6 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ $group->visit_date->format('d.m.Y H:i') }}
                </td>
                <td class="px-6 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ Str::limit($group->name, 50, '...') }}
                </td>
                <!-- Используем подсчитанное количество -->
                <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500">{{ $group->orders_count }}</td>
<!--                <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500">{{-- $group->taxi_way --}}</td>
                <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500">{{-- $group->taxi_price --}}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{-- $group->taxi_vozm --}}</td>-->
                <td class="px-6 py-2 whitespace-nowrap text-right text-sm font-medium">
                    <a href="{{ route('order-groups.show', $group) . '?' . http_build_query($urlParams) }}"  class="text-blue-600 hover:text-blue-900 mr-3" title="Просмотр">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </a>

                    <!-- Кнопка Редактировать -->
                    <a href="{{ route('order-groups.edit', $group) . '?' . http_build_query($urlParams) }}" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Редактировать">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </a>

                    <!-- Кнопка Удалить -->
                    <form action="{{ route('order-groups.destroy', $group) }}" method="POST" class="inline-block" onsubmit="return confirm('Вы уверены, что хотите удалить эту группу?')" 
                          title="Удалить группу">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </form>
                </td>
                <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500">{{ $group->created_at->format('d.m.Y H:i') }}</td>
                
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">Группы заказов не найдены.</td>
            </tr>
            @endforelse
        </tbody>
    </table>    