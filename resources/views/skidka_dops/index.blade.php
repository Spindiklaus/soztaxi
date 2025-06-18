<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Дополнительные условия</h1>

            <!-- Кнопка создания записи -->
            <a href="{{ route('skidka_dops.create') }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150"
               title="Создать запись">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Добавить запись
            </a>

            <!-- Фильтры -->
            <form action="{{ route('skidka_dops.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label for="filter_name" class="block text-sm font-medium text-gray-700">Наименование</label>
                    <input type="text" name="name" id="filter_name" value="{{ request('name') }}" placeholder="%Поиск%" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="filter_life" class="block text-sm font-medium text-gray-700">Действующий</label>
                    <select name="life" id="filter_life" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Все</option>
                        <option value="1" {{ request('life') == '1' ? 'selected' : '' }}>Да</option>
                        <option value="0" {{ request('life') == '0' ? 'selected' : '' }}>Нет</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-2 col-span-full">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                        Применить фильтр
                    </button>
                    <a href="{{ route('skidka_dops.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-gray-800 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
                        Очистить фильтр
                    </a>
                </div>
            </form>

            <!-- Таблица -->
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-800 text-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">Наименование</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">Скидка (%)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">Лимит поездок</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">Действующий</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Действия</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">id</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($skidkaDops as $skidkaDop)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $skidkaDop->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $skidkaDop->skidka }}%</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $skidkaDop->kol_p ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($skidkaDop->life)
                                <span class="text-green-600">Да</span>
                            @else
                                <span class="text-red-600">Нет</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                            <a href="{{ route('skidka_dops.edit', $skidkaDop) }}" class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-md hover:bg-yellow-200">Редактировать</a>
                            <form action="{{ route('skidka_dops.destroy', $skidkaDop) }}" method="POST" class="inline" onsubmit="return confirm('Вы уверены?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-100 text-red-800 rounded-md hover:bg-red-200">Удалить</button>
                            </form>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $skidkaDop->id }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Пагинация -->
            <div class="mt-4">
                {{ $skidkaDops->appends(request()->all())->links() }}
            </div>
        </div>
    </div>
</x-app-layout>