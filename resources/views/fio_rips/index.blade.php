<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Записи о RIP</h1>
            <!-- Кнопка создания записи -->
            <a href="{{ route('fio_rips.create') }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150"
               title="Создать запись">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Добавить запись
            </a>

            <!-- Кнопка импорта -->
            <a href="{{ route('import.fio_rips.form') }}"
               class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700"
               title="Импортировать записи">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Импортировать CSV
            </a>

            <!-- Фильтры -->
            <form action="{{ route('fio_rips.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label for="filter_fio" class="block text-sm font-medium text-gray-700">ФИО</label>
                    <input type="text" name="fio" id="filter_fio" value="{{ request('fio') }}" placeholder="%Поиск по ФИО%" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="filter_kl_id" class="block text-sm font-medium text-gray-700">ID клиента</label>
                    <input type="text" name="kl_id" id="filter_kl_id" value="{{ request('kl_id') }}" placeholder="Серия^номер паспорта" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="filter_sex" class="block text-sm font-medium text-gray-700">Пол</label>
                    <select name="sex" id="filter_sex" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Все</option>
                        <option value="М" {{ request('sex') == 'М' ? 'selected' : '' }}>Мужской</option>
                        <option value="Ж" {{ request('sex') == 'Ж' ? 'selected' : '' }}>Женский</option>
                    </select>
                </div>
                <!-- Кнопки "Применить фильтр" и "Очистить фильтр" -->
                <div class="flex justify-end space-x-2 col-span-full">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                        Применить фильтр
                    </button>
                    <a href="{{ route('fio_rips.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-gray-800 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
                        Очистить фильтр
                    </a>
                </div>

            </form>

            <!-- Таблица -->
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-800 text-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">ФИО</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">ID клиента</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">Дата рождения</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">Пол</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">Дата смерти</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">Номер записи ЗАГС</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($fioRips as $fioRip)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $fioRip->fio }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $fioRip->kl_id ?: '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ optional($fioRip->data_r)->format('d.m.Y') ?: '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($fioRip->sex === 'М')
                            <span class= "text-blue-700">
                                @else        
                                <span class= "text-pink-700">
                                    @endif        
                                    {{ $fioRip->sex === 'М' ? 'Мужской' : ($fioRip->sex === 'Ж' ? 'Женский' : '-') }}                                
                                </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ optional($fioRip->rip_at)->format('d.m.Y') ?: '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $fioRip->nom_zap ?: '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                            <a href="{{ route('fio_rips.edit', $fioRip) }}" class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-md hover:bg-yellow-200">Редактировать</a>
                            <form action="{{ route('fio_rips.destroy', $fioRip) }}" method="POST" class="inline" onsubmit="return confirm('Вы уверены?')">
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
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                {{ $fioRips->appends(request()->all())->links() }}
            </div>
        </div>
    </div>
</x-app-layout>