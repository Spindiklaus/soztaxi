<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Заголовок + кнопка создания -->
            <div class="flex justify-between items-center mb-2">
                <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-600" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 13l4 4L19 7" />
                    </svg>
                    Роли
                </h1>
                <a href="{{ route('roles.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150"
                   title="Создать роль">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4v16m8-8H4" />
                    </svg>
                    Добавить роль
                </a>
            </div>

<!--             Форма фильтрации 
            <div class="bg-white shadow rounded-lg p-4 mb-2">
                <form action="{{ route('roles.index') }}" method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                         Поиск по названию 
                        <div>
                            <label for="filter_name" class="block text-sm font-medium text-gray-700">Название</label>
                            <input type="text" name="name" id="filter_name"
                                   value="{{ request('name') }}"
                                   placeholder="%Поиск по названию%"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                     Кнопки 
                    <div class="flex justify-end space-x-2 pt-2">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Применить фильтр
                        </button>
                        <a href="{{-- route('roles.index') --}}"
                           class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                            Очистить фильтр
                        </a>
                    </div>
                </form>
            </div>-->

            <!-- Таблица ролей -->
            <div x-data='{
                sortField: "{{ $sort ?? "id" }}",
                sortDirection: "{{ $direction ?? "asc" }}",
                search: "{{ $search ?? "" }}",
                roles: @json($roles->map(fn ($r) => ["id" => $r->id, "name" => $r->name])),
                get filteredRoles() {
                    if (!this.search) return this.roles;
                    const term = this.search.toLowerCase();
                    return this.roles.filter(r => r.name.toLowerCase().includes(term));
                },
                sortBy(field) {
                    if (this.sortField === field) {
                        this.sortDirection = this.sortDirection === "asc" ? "desc" : "asc";
                    } else {
                        this.sortField = field;
                        this.sortDirection = "asc";
                    }
                    let url = "?sort=" + field + "&direction=" + this.sortDirection +
                              (this.search ? "&name=" + encodeURIComponent(this.search) : "");
                    window.location.href = url;
                },
                deleteRole(id) {
                    if (!confirm("Вы уверены?")) return;
                    fetch(`/roles/${id}`, {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content,
                            "Content-Type": "application/json"
                        }
                    }).then(res => {
                        if (res.ok) {
                            this.roles = this.roles.filter(r => r.id !== id);
                        } else {
                            alert("Ошибка при удалении");
                        }
                    });
                }
            }' x-cloak class="bg-white rounded-lg overflow-auto max-h-[70vh] border border-gray-300">
                <table class="min-w-full divide-y divide-gray-200 bg-white">
                    <thead class="bg-blue-800 text-gray-200 sticky top-0 z-10 shadow-lg">
                        <tr>
                            <th @click="sortBy('id')" scope="col"
                                class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">
                                ID
                                <span class="ml-1" x-show="sortField === 'id' && sortDirection === 'asc'">↑</span>
                                <span class="ml-1" x-show="sortField === 'id' && sortDirection === 'desc'">↓</span>
                            </th>
                            <th @click="sortBy('name')" scope="col"
                                class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">
                                Название
                                <span class="ml-1" x-show="sortField === 'name' && sortDirection === 'asc'">↑</span>
                                <span class="ml-1" x-show="sortField === 'name' && sortDirection === 'desc'">↓</span>
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider">
                                Действия
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <template x-for="role in filteredRoles" :key="role.id">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="role.id"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="role.name"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2 flex justify-end">
                                    <a :href="`/roles/${role.id}`"
                                       class="inline-flex items-center px-2 py-1 rounded-md text-sm font-medium bg-blue-100 text-blue-800 hover:bg-blue-200"
                                       title="Просмотр">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>
                                    <a :href="`/roles/${role.id}/edit`"
                                       class="inline-flex items-center px-2 py-1 rounded-md text-sm font-medium bg-yellow-100 text-yellow-800 hover:bg-yellow-200"
                                       title="Редактировать">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </a>
                                    <form class="inline" x-on:submit.prevent="deleteRole(role.id)">
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

<!--             Сообщение при отсутствии результатов 
            <div x-show="filteredRoles.length === 0 && search.trim() !== ''"
                 class="bg-white border border-gray-300 rounded-lg p-4 mt-2 text-center text-gray-500">
                Ничего не найдено по запросу "<span x-text="search"></span>"
            </div>-->

<!--             Пагинация 
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                {{ $roles->appends(['sort' => request('sort'), 'direction' => request('direction'), 'name' => request('name')])->links() }}
            </div>-->

        </div>
    </div>

<!--     CSRF Token для AJAX 
    <meta name="csrf-token" content="{{ csrf_token() }}">-->
</x-app-layout>