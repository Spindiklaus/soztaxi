{{-- resources/views/fiodtrns/index.blade.php --}}

<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('import_errors'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                    <p class="font-bold">Ошибка при импорте</p>
                    <ul class="list-disc pl-5 mt-2">
                        @foreach(session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success_count'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                    {{ session('success_count') }} клиент(ов) успешно импортировано.
                </div>
            @endif

            <!-- Заголовок + кнопка создания -->
            <div class="flex justify-between items-center mb-2">
                <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-600" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v3m0 0v3m0-3h3m-3 0H9m4-12h5a2 2 0 012 2v10a2 2 0 01-2 2h-5l-5 5v-5H2a2 2 0 01-2-2V7a2 2 0 012-2h5V5z" />
                    </svg>
                    Клиенты соцтакси
                </h1>
                <div class="space-x-2 flex">
                    <a href="{{ route('import.fiodtrns.form') }}"
                       class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700"
                       accesskey="" title="Импортировать клиентов">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Импортировать CSV
                    </a>
                    <a href="{{ route('fiodtrns.create', ['sort' => request('sort'), 'direction' => request('direction')]) }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700"
                       title="Создать клиента">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Добавить клиента
                    </a>
                </div>    
            </div>

            <!-- Раскрывающийся список дубликатов -->
            @if($duplicateCounts->isNotEmpty())
            <div x-data="{ open: false }" class="mb-2">
                <button @click="open = !open"
                         class="w-full text-left flex justify-between items-center bg-yellow-50 border border-yellow-200 rounded-lg p-4 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    <span class="font-bold text-yellow-700">
                        Найдены дубликаты по ФИО (не RIP): {{ $duplicateCounts->count() }} совп.
                    </span>
                    <svg :class="{ 'rotate-180': open }"
                         class="w-5 h-5 text-yellow-700 transition-transform duration-200"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" x-collapse class="mt-2 border border-yellow-200 rounded-b-lg bg-white">
                    <ul class="divide-y divide-gray-200">
                        @foreach($duplicateCounts as $fio => $count)
                            <li class="p-3 text-sm text-gray-700 hover:bg-gray-50">
                                <span class="font-medium">{{ $fio }}</span> ({{ $count }} чел.)
                            </li>
                        @endforeach
                    </ul>
                    <!-- Кнопка "Совместить дубликаты" -->
                    <div class="p-3 border-t border-gray-200">
                        <a href="{{ route('fiodtrns.merge.form') }}"
                           class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-white hover:bg-purple-700"
                           title="Открыть форму совмещения">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            Совместить дубликаты
                        </a>
                    </div>
                </div>
            </div>
            @else
                <!-- Опционально: сообщение, если дубликатов нет -->
                <div class="mb-4 text-sm text-gray-600">
                    Дубликатов по ФИО (без даты RIP) не найдено.
                </div>
            @endif


            <!-- Форма фильтрации -->
            <div class="bg-white shadow rounded-lg p-2 mb-2">
                <form action="{{ route('fiodtrns.index') }}" method="GET" class="flex flex-wrap items-end gap-3">
                     <!-- Скрытое поле для сохранения сортировки и других параметров -->
                    @if(request('sort'))
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                    @endif
                    @if(request('direction'))
                        <input type="hidden" name="direction" value="{{ request('direction') }}">
                    @endif
                    <!-- Конец скрытых полей -->
                    
                    <!-- Изменяем grid-cols с 4 на 6, чтобы освободить место для кнопок -->
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4 w-full">
                        <!-- Поиск по ФИО -->
                        <div class="flex-1 min-w-[200px] md:col-span-2">
                            <label for="filter_fio" class="block text-sm font-medium text-gray-700">ФИО</label>
                            <input type="text" name="filter_fio" id="filter_fio"
                                   value="{{ $filterFio }}"
                                   placeholder="%Поиск по ФИО%"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <!-- Поиск по ID клиента -->
                        <div class="flex-1 min-w-[150px]">
                            <label for="filter_kl_id" class="block text-sm font-medium text-gray-700">ID клиента</label>
                            <input type="text" name="filter_kl_id" id="filter_kl_id"
                                   value="{{ $filterKlId }}"
                                   placeholder="Серия^номер паспорта"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <!-- Фильтр по полу -->
                        <div class="flex-1 min-w-[120px]">
                            <label for="filter_sex" class="block text-sm font-medium text-gray-700">Пол</label>
                            <select name="filter_sex" id="filter_sex"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Все</option>
                                <option value="М" {{ request('sex') == 'М' ? 'selected' : '' }}>Мужской</option>
                                <option value="Ж" {{ request('sex') == 'Ж' ? 'selected' : '' }}>Женский</option>
                            </select>
                        </div>
                        <!-- Фильтр RIP -->
                        <div class="flex items-end pb-1 md:col-span-2">
                            <label for="filter_rip" class="block text-sm font-medium text-gray-700 mr-2">Только с RIP</label>
                            <input type="checkbox" name="rip" id="filter_rip" value="1"
                                   {{ $ripFilter == 1 ? 'checked' : '' }}
                                   class="rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label for="date_from" class="block text-sm font-medium text-gray-700">Дата поездки с</label>
                            <input type="date" name="date_from" id="date_from"
                                   value="{{ request('date_from') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label for="date_to" class="block text-sm font-medium text-gray-700">Дата поездки по</label>
                            <input type="date" name="date_to" id="date_to"
                                   value="{{ request('date_to') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <!-- Кнопки перемещены сюда -->
                        <div class="flex justify-end space-x-2 pt-4 md:col-start-5 md:col-end-7">
                             <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Применить фильтр
                            </button>
                            <a href="{{ route('fiodtrns.index') }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                                Очистить фильтр
                            </a>
                        </div>
                        <!-- Конец кнопок -->
                    </div>
                    <!-- Конец сетки -->
                </form>
            </div>
            
            <div class="mt-2 mb-2">
                {{ $fiodtrns->appends(request()->all())->links() }}
            </div>

            <!-- Таблица клиентов -->
             <div x-data="{
                 sortField: '{{ $sort ?? 'id' }}',
                 sortDirection: '{{ $direction ?? 'asc' }}',
                 sortBy(field) {
                 if (this.sortField === field) {
                 this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                 } else {
                 this.sortField = field;
                 this.sortDirection = 'asc';
                 }

                 // Получаем текущие параметры URL
                 let url = new URL(window.location.href);
                 let params = new URLSearchParams(url.search);

                 // Устанавливаем параметры сортировки
                 params.set('sort', field);
                 params.set('direction', this.sortDirection);

                 // Формируем новый URL с сохранением всех параметров
                 url.search = params.toString();

                 window.location.href = url.toString();
                 }
                 }" x-cloak class="bg-white rounded-lg overflow-auto max-h-[70vh] border border-gray-300">
                <table class="min-w-full divide-y divide-gray-200 bg-white">
                    <thead class="bg-blue-800 text-gray-200 sticky top-0 z-10 shadow-lg">
                        <tr>
                            <th @click="sortBy('fio')" scope="col"
                                 class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">
                                ФИО
                                <span class="ml-1" x-show="sortField === 'fio' && sortDirection === 'asc'">↑</span>
                                <span class="ml-1" x-show="sortField === 'fio' && sortDirection === 'desc'">↓</span>
                            </th>
                            <th @click="sortBy('kl_id')" scope="col"
                                 class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">
                                ID клиента
                                <span class="ml-1" x-show="sortField === 'kl_id' && sortDirection === 'asc'">↑</span>
                                <span class="ml-1" x-show="sortField === 'kl_id' && sortDirection === 'desc'">↓</span>
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                Удостоверение
                            </th>
                            <th @click="sortBy('data_r')" scope="col"
                                 class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">
                                Дата рождения
                                <span class="ml-1" x-show="sortField === 'data_r' && sortDirection === 'asc'">↑</span>
                                <span class="ml-1" x-show="sortField === 'data_r' && sortDirection === 'desc'">↓</span>
                            </th>
                            <th @click="sortBy('sex')" scope="col"
                                 class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">
                                Пол
                                <span class="ml-1" x-show="sortField === 'sex' && sortDirection === 'asc'">↑</span>
                                <span class="ml-1" x-show="sortField === 'sex' && sortDirection === 'desc'">↓</span>
                            </th>
                            <th @click="sortBy('rip_at')" scope="col"
                                 class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">
                                RIP дата
                                <span class="ml-1" x-show="sortField === 'rip_at' && sortDirection === 'asc'">↑</span>
                                <span class="ml-1" x-show="sortField === 'rip_at' && sortDirection === 'desc'">↓</span>
                            </th>
                            <th @click="sortBy('orders_count')" scope="col"
                                 class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">
                                Кол-во поездок
                                <span class="ml-1" x-show="sortField === 'orders_count' && sortDirection === 'asc'">↑</span>
                                <span class="ml-1" x-show="sortField === 'orders_count' && sortDirection === 'desc'">↓</span>
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider">
                                Действия
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                Оператор
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        {{-- Используем @forelse для отображения данных от Laravel --}}
                        @forelse ($fiodtrns as $fiodtrn)
                        <tr>
                            <td class="{{ $fiodtrn->rip_at ? 'px-4 py-2 whitespace-nowrap text-sm bg-gray-500' : 'px-6 py-4 whitespace-nowrap text-sm text-gray-900' }}">
                                <div>{{ $fiodtrn->fio }}</div>
                                @if($fiodtrn->komment)
                                    <div class="text-xs text-gray-600 mt-1">{{ \Str::limit($fiodtrn->komment, 100) }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $fiodtrn->kl_id }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $fiodtrn->client_invalid }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ optional($fiodtrn->data_r)->format('d.m.Y') }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                <span class="{{ $fiodtrn->sex === 'М' ? 'text-blue-700' : ($fiodtrn->sex === 'Ж' ? 'text-pink-700' : 'text-gray-500') }}">
                                    {{ $fiodtrn->sex === 'М' ? 'Мужской' : ($fiodtrn->sex === 'Ж' ? 'Женский' : '-') }}
                                </span>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ optional($fiodtrn->rip_at)->format('d.m.Y') }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                @if($fiodtrn->orders_count > 0)
                                    <a href="{{ route('fiodtrns.orders', array_merge(['fiodtrn' => $fiodtrn], $urlParams)) }}" target ="_blank"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200">
                                            {{ $fiodtrn->orders_count }}
                                    </a>
                                @endif
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-right text-sm space-x-2 flex justify-end">
                                <a href="{{ route('fiodtrns.show', array_merge(['fiodtrn' => $fiodtrn], $urlParams)) }}"
                                   class="inline-flex items-center px-2 py-1 rounded-md text-sm font-medium bg-blue-100 text-blue-800 hover:bg-blue-200"
                                   title="Просмотр">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                         stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </a>
                                <a href="{{ route('fiodtrns.edit', array_merge(['fiodtrn' => $fiodtrn], $urlParams)) }}" 
                                   class="inline-flex items-center px-2 py-1 rounded-md text-sm font-medium bg-yellow-100 text-yellow-800 hover:bg-yellow-200"
                                   title="Редактировать">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                         stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </a>

                                <!-- Удаление -->
                                <form action="{{ route('fiodtrns.destroy', $fiodtrn) }}?{{ http_build_query($urlParams) }}"
                                      method="POST"
                                      class="inline"
                                      onsubmit="return confirm('Вы уверены, что хотите удалить этого клиента?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center px-2 py-1 rounded-md text-sm font-medium bg-red-100 text-red-800 hover:bg-red-200"
                                            title="Удалить">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                             class="feather feather-trash-2">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="9" y1="12" x2="9" y2="18"></line>
                                            <line x1="15" y1="12" x2="15" y2="18"></line>
                                        </svg>
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ optional($fiodtrn->user)->name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                Клиенты не найдены
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!--
                         Сообщение при отсутствии результатов 
                        <div x-show="filteredFioDtrns.length === 0 && (searchFio.trim() !== '' || searchKlId.trim() !== '' || sexFilter)"
                             class="bg-white border border-gray-300 rounded-lg p-4 mt-2 text-center text-gray-500">
                            Ничего не найдено по запросу "<span x-text="searchFio"></span>" / "<span x-text="searchKlId"></span>"
                        </div>-->

            <!-- Пагинация -->
            <div class="mt-4">
                {{ $fiodtrns->appends(request()->all())->links() }}
            </div>
        </div>
    </div>
</x-app-layout>