<!-- Это файл: resources/views/taxis/index.blade.php -->
<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Заголовок + кнопка создания -->
            <div class="flex justify-between items-center mb-2">
                <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-600" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                    Операторы такси
                </h1>
                <div class="space-x-2 flex">
                    <a href="{{ route('import.taxis.form') }}"
                       class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700"
                       title="Импортировать операторов такси из CSV">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Импортировать CSV
                    </a>
                    <a href="{{ route('taxis.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150"
                       title="Создать оператора такси">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 4v16m8-8H4" />
                        </svg>
                        Добавить оператора такси
                    </a>
                </div>
            </div>

            <!-- Форма фильтрации -->
            <div class="bg-white shadow rounded-lg p-4 mb-2">
                <form action="{{ route('taxis.index') }}" method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Поиск по названию -->
                        <div>
                            <label for="filter_name" class="block text-sm font-medium text-gray-700">Название</label>
                            <input type="text" name="name" id="filter_name"
                                   value="{{ request('name') }}"
                                   placeholder="%Поиск по названию%"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <!-- Поиск по статусу -->
                        <div>
                            <label for="filter_life" class="block text-sm font-medium text-gray-700">Статус</label>
                            <select name="life" id="filter_life"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Все</option>
                                <option value="1" {{ request('life') == '1' ? 'selected' : '' }}>Активен</option>
                                <option value="0" {{ request('life') == '0' ? 'selected' : '' }}>Не активен</option>
                            </select>
                        </div>
                    </div>
                    <!-- Кнопки -->
                    <div class="flex justify-end space-x-2 pt-2">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Применить фильтр
                        </button>
                        <a href="{{ route('taxis.index') }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                            Очистить фильтр
                        </a>
                    </div>
                </form>
            </div>

            <!-- Таблица таксистов -->
            <div x-data='{
                 sortField: "{{ $sort ?? "id" }}",
                 sortDirection: "{{ $direction ?? "asc" }}",
                 search: "{{ request("name") ?? "" }}",
                 lifeFilter: "{{ request("life") ?? "" }}",
                 taxis: @json($taxisJs),
                 get filteredTaxis() {
                 return this.taxis.filter(t => {
                 const matchesSearch = !this.search || t.name.toLowerCase().includes(this.search.toLowerCase());
                 const matchesLife = !this.lifeFilter || t.life == this.lifeFilter;
                 return matchesSearch && matchesLife;
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
                 (this.search ? "&name=" + encodeURIComponent(this.search) : "") +
                 (this.lifeFilter !== "" ? "&life=" + encodeURIComponent(this.lifeFilter) : "");
                 window.location.href = url;
                 },
                 deleteTaxi(id) {
                 if (!confirm("Вы уверены?")) return;
                 fetch(`/taxis/${id}`, {
                 method: "DELETE",
                 headers: {
                 "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content,
                 "Content-Type": "application/json"
                 }
                 }).then(res => {
                 if (res.ok) {
                 this.taxis = this.taxis.filter(t => t.id !== id);
                 } else {
                 alert("Ошибка при удалении");
                 }
                 });
                 }
                 }' x-cloak class="bg-white rounded-lg overflow-auto max-h-[70vh] border border-gray-300">
                <table class="min-w-full divide-y divide-gray-200 bg-white">
                    <thead class="bg-blue-800 text-gray-200 sticky top-0 z-10 shadow-lg">
                        <tr>
                            <th @click="sortBy('name')" scope="col"
                                 class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">
                                Оператор такси (наименование)
                                <span class="ml-1" x-show="sortField === 'name' && sortDirection === 'asc'">↑</span>
                                <span class="ml-1" x-show="sortField === 'name' && sortDirection === 'desc'">↓</span>
                            </th>
                            <th @click="sortBy('koef')" scope="col"
                                 class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">
                                Стоимость 1 км пути
                                <span class="ml-1" x-show="sortField === 'koef' && sortDirection === 'asc'">↑</span>
                                <span class="ml-1" x-show="sortField === 'koef' && sortDirection === 'desc'">↓</span>
                            </th>
                            <th @click="sortBy('posadka')" scope="col"
                                 class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">
                                Стоимость посадки
                                <span class="ml-1" x-show="sortField === 'posadka' && sortDirection === 'asc'">↑</span>
                                <span class="ml-1" x-show="sortField === 'posadka' && sortDirection === 'desc'">↓</span>
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                Комментарии
                            </th>
                            <th @click="sortBy('id')" scope="col"
                                 class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">
                                id
                                <span class="ml-1" x-show="sortField === 'name' && sortDirection === 'asc'">↑</span>
                                <span class="ml-1" x-show="sortField === 'name' && sortDirection === 'desc'">↓</span>
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                Статус
                            </th>

                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider">
                                Действия
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <template x-for="taxi in filteredTaxis" :key="taxi.id">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="taxi.name"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="taxi.koef"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="taxi.posadka"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" 
                                    x-text="taxi.komment ? (taxi.komment.length > 40 ? taxi.komment.substring(0, 40) + '...' : taxi.komment) : ''"
                                    title="taxi.komment || ''">
                                </td>                                
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="taxi.id"></td>   
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span x-text="taxi.life == 1 ? 'Активен' : 'Не активен'"
                                          :class="taxi.life == 1 ? 'inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs font-bold' : 'inline-flex items-center px-2 py-1 rounded-full bg-red-100 text-red-800 text-xs font-bold'">
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2 flex justify-end">
                                    <a :href="`/taxis/${taxi.id}`"
                                       class="inline-flex items-center px-2 py-1 rounded-md text-sm font-medium bg-blue-100 text-blue-800 hover:bg-blue-200"
                                       title="Просмотр">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>
                                    <a :href="`/taxis/${taxi.id}/edit`"
                                       class="inline-flex items-center px-2 py-1 rounded-md text-sm font-medium bg-yellow-100 text-yellow-800 hover:bg-yellow-200"
                                       title="Редактировать">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </a>
                                    <form class="inline" x-on:submit.prevent="deleteTaxi(taxi.id)">
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


            <!-- Пагинация -->
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                {{ $taxis->appends(['sort' => request('sort'), 'direction' => request('direction'), 'name' => request('name'), 'life' => request('life')])->links() }}
            </div>

        </div>
    </div>

    <!-- Alpine.js Scripts -->
    <script>
        // Для отправки CSRF токена в fetch
        document.addEventListener('alpine:init', () => {
            Alpine.data('taxiData', () => ({
                    search: '',
                    taxis: [],
                    get filteredTaxis() {
                        return this.taxis.filter(t =>
                            t.name.toLowerCase().includes(this.search.toLowerCase())
                        );
                    }
                }));
        });
    </script>
</x-app-layout>