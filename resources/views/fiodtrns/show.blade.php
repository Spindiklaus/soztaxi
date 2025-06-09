<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Создать клиента</h1>

            <form action="{{ route('clients.store') }}" method="POST" class="bg-white shadow rounded-lg p-6 space-y-6">
                @csrf

                <!-- ID клиента -->
                <div>
                    <label for="kl_id" class="block text-sm font-medium text-gray-700">ID клиента (серия^номер)</label>
                    <input type="text" name="kl_id" id="kl_id" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500">
                </div>

                <!-- ФИО -->
                <div>
                    <label for="fio" class="block text-sm font-medium text-gray-700">ФИО</label>
                    <input type="text" name="fio" id="fio" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500">
                </div>

                <!-- Дата рождения и пол -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="data_r" class="block text-sm font-medium text-gray-700">Дата рождения</label>
                        <input type="date" name="data_r" id="data_r"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500">
                    </div>
                    <div>
                        <label for="sex" class="block text-sm font-medium text-gray-700">Пол</label>
                        <select name="sex" id="sex"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500">
                            <option value="">Не указан</option>
                            <option value="M">Мужской</option>
                            <option value="F">Женский</option>
                        </select>
                    </div>
                </div>

                <!-- Комментарий -->
                <div>
                    <label for="komment" class="block text-sm font-medium text-gray-700">Комментарии</label>
                    <textarea name="komment" id="komment" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500"></textarea>
                </div>

                <!-- Кнопки -->
                <div class="flex justify-end space-x-2 pt-2">
                    <a href="{{ route('clients.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                        Отменить
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>