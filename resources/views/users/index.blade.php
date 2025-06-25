<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Заголовок + кнопка создания -->
            <div class="flex justify-between items-center mb-2">
                <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Пользователи
                </h1>

                <a href="{{ route('users.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150"
                   title="Создать пользователя">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Добавить пользователя
                </a>
            </div>

            <!-- Форма фильтрации -->
            <div class="bg-white shadow rounded-lg p-4 mb-2">
                <form action="{{ route('users.index') }}" method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Поиск по имени -->
                        <div>
                            <label for="filter_name" class="block text-sm font-medium text-gray-700">Имя</label>
                            <input type="text" name="name" id="filter_name" value="{{ request('name') }}" placeholder="%Поиск по имени%" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <!-- Поиск по email -->
                        <div>
                            <label for="filter_email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="text" name="email" id="filter_email" value="{{ request('email') }}" placeholder="%Поиск по email%" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <!-- Фильтр по статусу (активен/неактивен) -->
                        <div>
                            <label for="filter_life" class="block text-sm font-medium text-gray-700">Действующий</label>
                            <select name="life" id="filter_life" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Все</option>
                                <option value="1" {{ request('life') == '1' ? 'selected' : '' }}>Да</option>
                                <option value="0" {{ request('life') == '0' ? 'selected' : '' }}>Нет</option>
                            </select>
                        </div>
                    </div>

                    <!-- Кнопки -->
                    <div class="flex justify-end space-x-2 pt-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Применить фильтр
                        </button>
                        <a href="{{ route('users.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                            Очистить фильтр
                        </a>
                    </div>
                </form>
            </div>

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
                 let url = '?sort=' + field + '&direction=' + this.sortDirection;
                 console.log('Redirecting to:', url); // <-- Отладка
                 window.location.href = url;
                 }
                 }" x-cloak class="bg-white rounded-lg overflow-auto max-h-[70vh] border border-gray-300">

                <!-- Для отладки -->
                <div class="p-4 text-sm text-gray-600">
                    Текущая сортировка: <strong x-text="`Поле: ${sortField}, Направление: ${sortDirection}`"></strong>
                </div>    

                <!-- Таблица -->
                <table class="min-w-full divide-y divide-gray-200 bg-white border border-gray-300">
                    <thead class="bg-blue-800 text-gray-200 sticky top-0 z-10 shadow-lg">
                        <tr>
                            <th @click="sortBy('id')" scope="col" 
                                 class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">
                                ID
                                <span x-show="sortField === 'id' && sortDirection === 'asc'">↑</span>
                                <span x-show="sortField === 'id' && sortDirection === 'desc'">↓</span>
                            </th>
                            <th @click="sortBy('name')" scope="col" 
                                 class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">
                                Имя
                                <span x-show="sortField === 'name' && sortDirection === 'asc'">↑</span>
                                <span x-show="sortField === 'name' && sortDirection === 'desc'">↓</span>
                            </th>
                            <th @click="sortBy('email')" scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">
                                Email
                                <span x-show="sortField === 'email' && sortDirection === 'asc'">↑</span>
                                <span x-show="sortField === 'email' && sortDirection === 'desc'">↓</span>
                            </th>                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">Литера</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">Действующий</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">Роли</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($users as $user)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $user->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $user->litera ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($user->life)
                                <span class="text-green-600">Да</span>
                                @else
                                <span class="text-red-600">Нет</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($user->roles->isNotEmpty())
                                {{ $user->roles->pluck('name')->join(', ') }}
                                @else
                                Нет ролей
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">   
                                <a href="{{ route('users.edit', array_merge(['user' => $user->id], request()->query())) }}"
                                   class="inline-flex items-center px-2 py-1 rounded-md text-sm font-medium bg-yellow-100 text-yellow-800 hover:bg-yellow-200"
                                   title="Редактировать">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="feather feather-edit">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </a> 
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Вы уверены?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center px-2 py-1 rounded-md text-sm font-medium bg-red-100 text-red-800 hover:bg-red-200"
                                            title="Удалить">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="9" y1="12" x2="9" y2="18"></line>
                                            <line x1="15" y1="12" x2="15" y2="18"></line>
                                        </svg>
                                    </button>    
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>


            <!-- Пагинация -->
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                {{ $users->appends(['sort' => $sort, 'direction' => $direction])->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

