<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Редактировать пользователя</h1>
            
            <form action="{{ route('users.update', array_merge(['user' => $user->id], request()->all()))  }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <!-- Поле имени -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Имя</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Поле email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <!-- Поле пароля -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Новый пароль</label>
                    <input type="password" name="password" id="password" placeholder="Оставьте пустым, чтобы не менять" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                 <!-- Подтверждение пароля -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Подтвердите пароль</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Подтвердите новый пароль" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                 
                  <!-- Поле литеры -->
                <div>
                    <label for="litera" class="block text-sm font-medium text-gray-700">Литера</label>
                    <input type="text" name="litera" id="litera" value="{{ old('litera', $user->litera) }}" placeholder="Введите литеру" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Поле "Действующий" -->
                <div>
                    <label for="life" class="block text-sm font-medium text-gray-700">Действующий</label>
                    <select name="life" id="life" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="1" {{ old('life', $user->life) ? 'selected' : '' }}>Да</option>
                        <option value="0" {{ !$user->life ? 'selected' : '' }}>Нет</option>
                    </select>
                </div>
                 

                <!-- Выбор роли -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">Роль</label>
                    <select name="role" id="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Выберите роль --</option>
                        @foreach ($roles as $roleId => $roleName)
                            <option value="{{ $roleId }}" {{ $userRole == $roleId ? 'selected' : '' }}>
                                {{ $roleName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Кнопка отправки -->
                <div class="flex justify-end">
                    <!--<a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">-->
                    <a href="{{ route('users.index', request()->all()) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                        Отмена
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                        Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>