    <div class="min-h-screen bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Создать / Редактировать категорию</h1>

            <form method="POST" action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}" class="bg-white shadow rounded-lg p-6 space-y-6">
                @csrf
                @if ($category->exists)
                    @method('PUT')
                @endif

                <!-- NMV -->
                <div>
                    <label for="nmv" class="block text-sm font-medium text-gray-700">NMV</label>
                    <input type="number" name="nmv" id="nmv"
                           value="{{ old('nmv', $category->nmv ?? '') }}"
                           required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Название -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Название</label>
                    <input type="text" name="name" id="name"
                           value="{{ old('name', $category->name ?? '') }}"
                           required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Скидка (%) -->
                <div>
                    <label for="skidka" class="block text-sm font-medium text-gray-700">Скидка (%)</label>
                    <input type="number" name="skidka" id="skidka"
                           value="{{ old('skidka', $category->skidka ?? '') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Лимит поездок -->
                <div>
                    <label for="kol_p" class="block text-sm font-medium text-gray-700">Лимит поездок</label>
                    <input type="number" name="kol_p" id="kol_p"
                           value="{{ old('kol_p', $category->kol_p ?? '') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Оператор -->
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700">Оператор</label>
                    <select name="user_id" id="user_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach (\App\Models\User::all() as $user)
                            <option value="{{ $user->id }}"
                                {{ old('user_id', $category->user_id ?? '') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Соцтакси / Легковой авто / ГАЗель -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Соцтакси -->
                    <div>
                        <label for="is_soz" class="block text-sm font-medium text-gray-700">Используется в "соцтакси"?</label>
                        <select name="is_soz" id="is_soz"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="1" {{ old('is_soz', $category->is_soz ?? '') == 1 ? 'selected' : '' }}>Да</option>
                            <option value="0" {{ old('is_soz', $category->is_soz ?? '') == 0 ? 'selected' : '' }}>Нет</option>
                        </select>
                    </div>

                    <!-- Легковой авто -->
                    <div>
                        <label for="is_auto" class="block text-sm font-medium text-gray-700">Используется в "Легковом авто"?</label>
                        <select name="is_auto" id="is_auto"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="1" {{ old('is_auto', $category->is_auto ?? '') == 1 ? 'selected' : '' }}>Да</option>
                            <option value="0" {{ old('is_auto', $category->is_auto ?? '') == 0 ? 'selected' : '' }}>Нет</option>
                        </select>
                    </div>

                    <!-- ГАЗель -->
                    <div>
                        <label for="is_gaz" class="block text-sm font-medium text-gray-700">Используется в "ГАЗели"?</label>
                        <select name="is_gaz" id="is_gaz"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="1" {{ old('is_gaz', $category->is_gaz ?? '') == 1 ? 'selected' : '' }}>Да</option>
                            <option value="0" {{ old('is_gaz', $category->is_gaz ?? '') == 0 ? 'selected' : '' }}>Нет</option>
                        </select>
                    </div>
                </div>

                <!-- Комментарий -->
                <div>
                    <label for="komment" class="block text-sm font-medium text-gray-700">Комментарий</label>
                    <textarea name="komment" id="komment" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('komment', $category->komment ?? '') }}</textarea>
                </div>

                <!-- Кнопки -->
                <div class="flex justify-end space-x-3 pt-4">
                    <a href="{{ route('categories.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Отмена
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border bordergray-600 rounded-md shadow-sm text-sm font-medium gray-700 bg-blue-600 hover:bg-blue-700">
                        {{ $category->exists ? 'Обновить' : 'Создать' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
