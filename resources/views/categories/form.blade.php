<div class="min-h-screen bg-gray-100 py-6">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg p-6 space-y-6">
            
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Создать / Редактировать категорию</h1>

    <!--    <p class="text-sm text-gray-500">Текущая сортировка: {{-- request('sort') --}} / {{-- request('direction') --}}</p>        -->

            <form method="POST" action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}" class="bg-white shadow rounded-lg p-6 space-y-6">
                @csrf
                @if ($category->exists)
                    @method('PUT')
                @endif

                <!-- Скрытые поля для передачи текущей сортировки -->
                <input type="hidden" name="sort" value="{{ request('sort', 'id') }}">
                <input type="hidden" name="direction" value="{{ request('direction', 'asc') }}">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Левая колонка -->
                    <div class="space-y-6">
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
                            <select name="skidka" id="skidka"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="0" {{ old('skidka', $category->skidka ?? '') == '0' ? 'selected' : '' }}>0%</option>
                                <option value="50" {{ old('skidka', $category->skidka ?? '') == '50' ? 'selected' : '' }}>50%</option>
                                <option value="100" {{ old('skidka', $category->skidka ?? '') == '100' ? 'selected' : '' }}>100%</option>
                            </select>
                        </div>

                        <!-- Лимит поездок -->
                        <div>
                            <label for="kol_p" class="block text-sm font-medium text-gray-700">Лимит поездок</label>
                            <input type="number" name="kol_p" id="kol_p"
                                   value="{{ old('kol_p', $category->kol_p ?? '10') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <!-- Категория для расчета допскидок -->
                        <div>
                            <label for="kat_dop" class="block text-sm font-medium text-gray-700">Категория для расчета допскидок</label>
                            <input type="number" name="kat_dop" id="kat_dop"
                                   value="{{ old('kat_dop', $category->kat_dop ?? '2') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>  

                     <!-- Средняя колонка -->
                     <div class="space-y-6">

                        <!-- Оператор -->
                        <div>
                            <label for="operator" class="block text-sm font-medium text-gray-700">Оператор</label>
                            <input type="text"
                                   id="operator"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100"
                                   value="{{ auth()->user()->name }}"
                                   disabled readonly>
                        </div>

                        <!-- Соцтакси / Легковой авто / ГАЗель -->
                        <div class="space-y-4">
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
                    </div>

                    <!-- Правая колонка -->
                    <div class="space-y-6">
                        <!-- Комментарий -->
                        <div class="h-full">
                            <label for="komment" class="block text-sm font-medium text-gray-700">Комментарий</label>
                                <textarea name="komment" id="komment" rows="6"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('komment', $category->komment ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Кнопки -->
                <div class="flex justify-end space-x-3 pt-2">
                    <a href="{{ route('categories.index', [
                            'sort' => $sort ?? request('sort', 'id'),
                            'direction' => $direction ?? request('direction', 'asc')
                            ]) }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50
                           title="Вернуться к списку категорий">
                            Отмена
                    </a>
                    <button type="submit"
                                class="inline-flex items-center px-4 py-2 border bordergray-600 rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            {{ $category->exists ? 'Обновить' : 'Создать' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
