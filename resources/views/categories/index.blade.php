<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Заголовок + кнопка рядом -->
            <div class="flex justify-between items-center mb-2">
                <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                    Категории инвалидов
                </h1>
                <!-- Кнопка создания категории -->
                <a href="{{ route('categories.create', ['sort' => request('sort'), 'direction' => request('direction')]) }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150"
                   title="Создать категорию">
                    <!-- Иконка "+" -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Создать категорию
                </a>

                <!-- Кнопка импорта -->
                <a href="{{ route('import.categories.form', ['sort' => request('sort'), 'direction' => request('direction')]) }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700"
                   title="Импортировать категории из CSV">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Импортировать CSV
                </a>



            </div>            
            <!-- Форма фильтрации -->
            <!-- Форма фильтрации -->
<div class="bg-white shadow rounded-lg p-4 mb-2">
    <form action="{{ route('categories.index') }}" method="GET" class="space-y-4">
        <!-- Скрытое поле для сохранения сортировки -->
        @if(request('sort'))
            <input type="hidden" name="sort" value="{{ request('sort') }}">
        @endif
        @if(request('direction'))
            <input type="hidden" name="direction" value="{{ request('direction') }}">
        @endif
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Поиск по названию -->
            <div>
                <label for="filter_name" class="block text-sm font-medium text-gray-700">Название</label>
                <input type="text" name="name" id="filter_name"
                       value="{{ request('name') }}"
                       placeholder="%Поиск по названию%"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Поиск по NMV -->
            <div>
                <label for="filter_nmv" class="block text-sm font-medium text-gray-700">NMV</label>
                <input type="number" name="nmv" id="filter_nmv"
                       value="{{ request('nmv') }}"
                       placeholder="Фильтр по NMV"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Фильтр по скидке -->
            <div>
                <label for="filter_skidka" class="block text-sm font-medium text-gray-700">Скидка (%)</label>
                <select name="skidka" id="filter_skidka"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Все</option>
                    <option value="0" {{ request('skidka') == '0' ? 'selected' : '' }}>0%</option>
                    <option value="50" {{ request('skidka') == '50' ? 'selected' : '' }}>50%</option>
                    <option value="100" {{ request('skidka') == '100' ? 'selected' : '' }}>100%</option>
                </select>
            </div>
        </div>

        <!-- Кнопки -->
        <div class="flex justify-end space-x-2 pt-2">
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Применить фильтр
            </button>

            <a href="{{ route('categories.index', ['sort' => request('sort'), 'direction' => request('direction')]) }}"
                class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                Очистить фильтр
            </a>
        </div>
    </form>
</div>

            <!-- Таблица -->

            <div x-data="{
                 sortField: '{{ $sort ?? 'id' }}',
                 sortDirection: '{{ $direction ?? 'asc' }}',
                 init() {
                 console.log('sortField:', this.sortField);
                 console.log('sortDirection:', this.sortDirection);
                 },
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

                 console.log('Redirecting to:', url.toString());
                 window.location.href = url.toString();
                 }
                 }" x-cloak class="bg-white rounded-lg overflow-auto max-h-[70vh] border border-gray-300">
                <table class="min-w-full divide-y divide-gray-200 bg-white">
                    <thead class="bg-blue-800 text-gray-200 sticky top-0 z-10 shadow-lg">
                        <tr>
                            <th @click="sortBy('nmv')" scope="col"
                                 class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">
                                NMV
                                <span class="ml-1" x-show="sortField === 'nmv' && sortDirection === 'asc'">↑</span>
                                <span class="ml-1" x-show="sortField === 'nmv' && sortDirection === 'desc'">↓</span>
                            </th>

                            <th @click="sortBy('name')" scope="col"
                                 class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">
                                Название
                                <span class="ml-1" x-show="sortField === 'name' && sortDirection === 'asc'">↑</span>
                                <span class="ml-1" x-show="sortField === 'name' && sortDirection === 'desc'">↓</span>
                            </th>

                            <th @click="sortBy('skidka')" scope="col"
                                 class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">
                                Скидка
                                <span class="ml-1" x-show="sortField === 'skidka' && sortDirection === 'asc'">↑</span>
                                <span class="ml-1" x-show="sortField === 'skidka' && sortDirection === 'desc'">↓</span>
                            </th>

                            <th @click="sortBy('kol_p')" scope="col"
                                 class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">
                                Лимит поездок в месяц
                                <span class="ml-1" x-show="sortField === 'kol_p' && sortDirection === 'asc'">↑</span>
                                <span class="ml-1" x-show="sortField === 'kol_p' && sortDirection === 'desc'">↓</span>
                            </th>

                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                Категория для расчета допскидок
                            </th>

                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                Оператор
                            </th>                                

                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider">
                                Действия
                            </th>
                            <th @click="sortBy('id')" scope="col"
                                 class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">
                                ID
                                <span class="ml-1" x-show="sortField === 'id' && sortDirection === 'asc'">↑</span>
                                <span class="ml-1" x-show="sortField === 'id' && sortDirection === 'desc'">↓</span>
                            </th>                                
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <!-- Твои строки -->
                        @foreach ($categories as $category)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $category->nmv }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $category->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $category->skidka }}%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $category->kol_p }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $category->kat_dop }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $category->user?->name ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2 flex">
                                <a href="{{ route('categories.show', ['category' => $category, 'sort' => request('sort'), 'direction' => request('direction')]) }}" 
                                   class="inline-flex items-center px-2 py-1 rounded-md text-sm font-medium bg-blue-100 text-blue-800 hover:bg-blue-200"
                                   title="Просмотр">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="feather feather-eye">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </a>
                                <a href="{{ route('categories.edit', ['category' => $category, 'sort' => request('sort'), 'direction' => request('direction')]) }}" 
                                   class="inline-flex items-center px-2 py-1 rounded-md text-sm font-medium bg-yellow-100 text-yellow-800 hover:bg-yellow-200"
                                   title="Редактировать">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="feather feather-edit">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </a>
                                <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Вы уверены, что хотите удалить эту категорию?')">
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $category->id }}</td>                                
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Пагинация -->
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                {{ $categories->appends(request()->except('page'))->links() }}
            </div>
        </div>
    </div>
</x-app-layout>