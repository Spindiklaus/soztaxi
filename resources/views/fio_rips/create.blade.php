<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Добавить запись о смерти</h1>

            <form action="{{ route('fio_rips.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="kl_id" class="block text-sm font-medium text-gray-700">ID клиента (серия^номер)</label>
                    <input type="text" name="kl_id" id="kl_id" value="{{ old('kl_id') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="fio" class="block text-sm font-medium text-gray-700">ФИО</label>
                    <input type="text" name="fio" id="fio" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="data_r" class="block text-sm font-medium text-gray-700">Дата рождения</label>
                    <input type="date" name="data_r" id="data_r" value="{{ old('data_r') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="sex" class="block text-sm font-medium text-gray-700">Пол</label>
                    <select name="sex" id="sex" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Не указан</option>
                        <option value="М" {{ old('sex') == 'М' ? 'selected' : '' }}>Мужской</option>
                        <option value="Ж" {{ old('sex') == 'Ж' ? 'selected' : '' }}>Женский</option>
                    </select>
                </div>

                <div>
                    <label for="adres" class="block text-sm font-medium text-gray-700">Адрес</label>
                    <input type="text" name="adres" id="adres" value="{{ old('adres') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="rip_at" class="block text-sm font-medium text-gray-700">Дата смерти</label>
                    <input type="date" name="rip_at" id="rip_at" value="{{ old('rip_at') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="nom_zap" class="block text-sm font-medium text-gray-700">Номер записи ЗАГС</label>
                    <input type="text" name="nom_zap" id="nom_zap" value="{{ old('nom_zap') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="komment" class="block text-sm font-medium text-gray-700">Комментарии</label>
                    <textarea name="komment" id="komment" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('komment') }}</textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2">
                    <a href="{{ route('fio_rips.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                        Отменить
                    </a>
                   <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Сохранить</button>
                </div>   
            </form>
        </div>
    </div>
</x-app-layout>