<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Заказы</h1>

            <!-- Кнопка создания записи -->
            <a href="{{ route('social-taxi-orders.create') }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150"
               title="Создать запись">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Добавить заказ
            </a>

            <!-- Фильтры -->
            <form action="{{ route('social-taxi-orders.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label for="filter_pz_nom" class="block text-sm font-medium text-gray-700">Номер заказа</label>
                    <input type="text" name="pz_nom" id="filter_pz_nom" value="{{ request('pz_nom') }}" placeholder="%Поиск%" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="filter_type_order" class="block text-sm font-medium text-gray-700">Тип заказа</label>
                    <select name="type_order" id="filter_type_order" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Все</option>
                        <option value="1" {{ request('type_order') == '1' ? 'selected' : '' }}>Соцтакси</option>
                        <option value="2" {{ request('type_order') == '2' ? 'selected' : '' }}>Легковое авто</option>
                        <option value="3" {{ request('type_order') == '3' ? 'selected' : '' }}>ГАЗель</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-2 col-span-full">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                        Применить фильтр
                    </button>
                    <a href="{{ route('social-taxi-orders.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-gray-800 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
                        Очистить фильтр
                    </a>
                </div>
            </form>

            <!-- Таблица -->
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-800 text-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">Тип заказа</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">Номер заказа</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">Дата заказа</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">Откуда</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">Куда</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($orders as $order)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $order->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($order->type_order == 1)
                                Соцтакси
                            @elseif ($order->type_order == 2)
                                Легковое авто
                            @elseif ($order->type_order == 3)
                                ГАЗель
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $order->pz_nom }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $order->pz_data->format('d.m.Y H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $order->adres_otkuda }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $order->adres_kuda }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                            <a href="{{ route('social-taxi-orders.edit', $order) }}" class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-md hover:bg-yellow-200">Редактировать</a>
                            <form action="{{ route('social-taxi-orders.destroy', $order) }}" method="POST" class="inline" onsubmit="return confirm('Вы уверены?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-100 text-red-800 rounded-md hover:bg-red-200">Удалить</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Пагинация -->
            <div class="mt-4">
                {{ $orders->appends(request()->all())->links() }}
            </div>
        </div>
    </div>
</x-app-layout>