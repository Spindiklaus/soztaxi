<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Редактировать клиента</h1>

            <form action="{{ route('fiodtrns.update', $fiodtrn) }}" method="POST" class="bg-white shadow rounded-lg p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Скрытые поля для передачи текущей сортировки -->
                <input type="hidden" name="sort" value="{{ request('sort', 'id') }}">
                <input type="hidden" name="direction" value="{{ request('direction', 'asc') }}">

                <!-- ID клиента -->
                <div>
                    <label for="kl_id" class="block text-sm font-medium text-gray-700">ID клиента (серия^номер)</label>
                    <input type="text" name="kl_id" id="kl_id" value="{{ old('kl_id', $fiodtrn->kl_id) }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500">
                    @error('kl_id')
                    <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <!-- ФИО -->
                <div>
                    <label for="fio" class="block text-sm font-medium text-gray-700">ФИО</label>
                    <input type="text" name="fio" id="fio" value="{{ old('fio', $fiodtrn->fio) }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500">
                    @error('fio')
                    <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Дата рождения и пол -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="data_r" class="block text-sm font-medium text-gray-700">Дата рождения</label>
                        <input type="date" name="data_r" id="data_r"
                               value="{{ old('data_r', optional($fiodtrn->data_r)->format('Y-m-d')) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500">
                        @error('data_r')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label for="sex" class="block text-sm font-medium text-gray-700">Пол</label>
                        <select name="sex" id="sex"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500">
                            <option value="">Не указан</option>
                            <option value="М" {{ old('sex', $fiodtrn->sex) == 'М' ? 'selected' : '' }}>Мужской</option>
                            <option value="Ж" {{ old('sex', $fiodtrn->sex) == 'Ж' ? 'selected' : '' }}>Женский</option>
                        </select>
                        @error('sex')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Комментарий -->
                <div>
                    <label for="komment" class="block text-sm font-medium text-gray-700">Комментарии</label>
                    <textarea name="komment" id="komment" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500">{{ old('komment', $fiodtrn->komment) }}</textarea>
                </div>

                <!-- Кнопки -->
                <div class="flex justify-end space-x-2 pt-2">
                    <a href="{{ route('fiodtrns.index', ['sort' => request('sort', 'id'), 'direction' => request('direction', 'asc') ]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                        Отменить
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Сохранить изменения
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>