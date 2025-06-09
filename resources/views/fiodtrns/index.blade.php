<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
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
                    Клиенты
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

           

            <!-- Форма фильтрации -->
            <div class="bg-white shadow rounded-lg p-4 mb-2">
                <form action="{{ route('fiodtrns.index') }}" method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Поиск по ФИО -->
                        <div>
                            <label for="filter_fio" class="block text-sm font-medium text-gray-700">ФИО</label>
                            <input type="text" name="fio" id="filter_fio"
                                   value="{{ request('fio') }}"
                                   placeholder="%Поиск по ФИО%"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <!-- Поиск по ID клиента -->
                        <div>
                            <label for="filter_kl_id" class="block text-sm font-medium text-gray-700">ID клиента</label>
                            <input type="text" name="kl_id" id="filter_kl_id"
                                   value="{{ request('kl_id') }}"
                                   placeholder="Серия^номер паспорта"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <!-- Фильтр по полу -->
                        <div>
                            <label for="filter_sex" class="block text-sm font-medium text-gray-700">Пол</label>
                            <select name="sex" id="filter_sex"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Все</option>
                                <option value="M" {{ request('sex') == 'M' ? 'selected' : '' }}>Мужской</option>
                                <option value="F" {{ request('sex') == 'F' ? 'selected' : '' }}>Женский</option>
                            </select>
                        </div>
                    </div>
                    <!-- Кнопки -->
                    <div class="flex justify-end space-x-2 pt-2">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Применить фильтр
                        </button>
                        <a href="{{ route('fiodtrns.index') }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                            Очистить фильтр
                        </a>
                    </div>
                </form>
            </div>

            <!-- Таблица клиентов -->
            <div x-data='{
                 sortField: "{{ $sort ?? "id" }}",
                 sortDirection: "{{ $direction ?? "asc" }}",
                 searchFio: "{{ request("fio") ?? "" }}",
                 searchKlId: "{{ request("kl_id") ?? "" }}",
                 sexFilter: "{{ request("sex") ?? "" }}",
                 fiodtrns: @json($fiodtrnsJs),
                 get filteredFioDtrns() {
                 return this.fiodtrns.filter(c => {
                 const matchesFio = !this.searchFio || c.fio.toLowerCase().includes(this.searchFio.toLowerCase());
                 const matchesKlId = !this.searchKlId || c.kl_id.includes(this.searchKlId);
                 const matchesSex = !this.sexFilter || c.sex === this.sexFilter;
                 return matchesFio && matchesKlId && matchesSex;
                 });
                 },
                 sortBy(field) {
                 if (this.sortField === field) {
                 this.sortDirection = this.sortDirection === "asc" ? "desc" : "asc";
                 } else {
                 this.sortField = field;
                 this.sortDirection = "asc";
                 }
                 let url = "?sort=" + field + "&direction=" + this.sortDirection +
                 (this.searchFio ? "&fio=" + encodeURIComponent(this.searchFio) : "") +
                 (this.searchKlId ? "&kl_id=" + encodeURIComponent(this.searchKlId) : "") +
                 (this.sexFilter ? "&sex=" + encodeURIComponent(this.sexFilter) : "");
                 window.location.href = url;
                 }
                 }' x-cloak class="bg-white rounded-lg overflow-auto max-h-[70vh] border border-gray-300">
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
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                Комментарии
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                Оператор
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider">
                                Действия
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <template x-for="fiodtrn in filteredFioDtrns" :key="fiodtrn.id">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="fiodtrn.fio"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="fiodtrn.kl_id"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="fiodtrn.data_r"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span x-text="fiodtrn.sex === 'M' ? 'Мужской' : fiodtrn.sex === 'F' ? 'Женский' : '-'"
                                          :class="fiodtrn.sex === 'M' ? 'text-blue-700' : fiodtrn.sex === 'F' ? 'text-pink-700' : 'text-gray-500'">
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="fiodtrn.rip_at"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="fiodtrn.komment"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="fiodtrn.operator"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2 flex justify-end">
                                    <a :href="`/fiodtrns/${fiodtrn.id}`"
                                       class="inline-flex items-center px-2 py-1 rounded-md text-sm font-medium bg-blue-100 text-blue-800 hover:bg-blue-200"
                                       title="Просмотр">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>
                                    <a :href="`/fiodtrns/${fiodtrn.id}/edit`"
                                       class="inline-flex items-center px-2 py-1 rounded-md text-sm font-medium bg-yellow-100 text-yellow-800 hover:bg-yellow-200"
                                       title="Редактировать">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </a>
                                    <form class="inline" x-on:submit.prevent="deleteFioDtrn(fiodtrn.id)">
                                        <button type="submit"
                                                class="inline-flex items-center px-2 py-1 rounded-md text-sm font-medium bg-red-100 text-red-800 hover:bg-red-200"
                                                title="Удалить">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                 stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                <line x1="9" y1="12" x2="9" y2="18"></line>
                                                <line x1="15" y1="12" x2="15" y2="18"></line>
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        </template>
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
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                {{ $fiodtrns->appends([
                    'sort' => request('sort'),
                    'direction' => request('direction'),
                    'fio' => request('fio'),
                    'kl_id' => request('kl_id'),
                    'sex' => request('sex')
                ])->links() }}
            </div>
        </div>
    </div>

    <!-- Alpine.js Scripts -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('fiodtrnData', () => ({
                    searchFio: '',
                    searchKlId: '',
                    sexFilter: '',
                    fiodtrns: [],
                    get filteredFioDtrns() {
                        return this.fiodtrns.filter(c => {
                            const matchesFio = !this.searchFio || c.fio?.toLowerCase().includes(this.searchFio.toLowerCase());
                            const matchesKlId = !this.searchKlId || c.kl_id.includes(this.searchKlId);
                            const matchesSex = !this.sexFilter || c.sex === this.sexFilter;
                            return matchesFio && matchesKlId && matchesSex;
                        });
                    },
                    deleteFioDtrn(id) {
                        if (!confirm('Вы уверены?'))
                            return;
                        fetch(`/fiodtrns/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector("meta[name=csrf-token]").content,
                                'Content-Type': 'application/json'
                            }
                        }).then(res => {
                            if (res.ok) {
                                this.fiodtrns = this.fiodtrns.filter(c => c.id !== id);
                            } else {
                                alert('Ошибка при удалении');
                            }
                        });
                    }
                }));
        });
    </script>
</x-app-layout>