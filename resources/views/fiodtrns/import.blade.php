<x-app-layout>
    <!-- Отладка -->
    <?php // @dd(session('import_errors')) ?>

@if(session('success_count'))
    @dd(session('success_count'))
@endif
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Импорт клиентов из CSV</h1>

            <!-- Форма загрузки файла -->
            <form action="{{ route('import.fiodtrns.process') }}" method="POST" enctype="multipart/form-data"
                  class="bg-white shadow rounded-lg p-6 space-y-6">
                @csrf

                <!-- Описание -->
                <div class="text-sm text-gray-600">
                    <p>Загрузите CSV-файл для массового добавления клиентов.</p>
                    <p class="mt-1">Формат: <code>kl_id;fio;data_r;sex;rip_at;created_rip;komment</code></p>
                    <p class="mt-1">Все даты должны быть в формате <strong>дд.мм.гггг</strong>.</p>
                    <p class="mt-1">Пример:</p>
                    <pre class="bg-gray-100 p-2 rounded mt-1 text-xs">
kl_id;fio;data_r;sex;rip_at;created_rip;komment
AB^1234567;Иванов Иван;01.01.1990;М;;;"Комментарий"
CD^7654321;Петрова Елена;12.05.1985;Ж;10.05.2024 14:30;11.05.2024 15:00;С RIP
                    </pre>
                </div>

                <!-- Поле загрузки -->
                <div>
                    <label for="csv_file" class="block text-sm font-medium text-gray-700">CSV файл</label>
                    <input type="file" name="csv_file" id="csv_file" accept=".csv" required
                           class="mt-1 block w-full text-sm text-gray-500 border border-gray-300 rounded-md cursor-pointer">
                </div>

                <!-- Кнопки -->
                <div class="flex justify-end space-x-2 pt-6">
                    <a href="{{ route('fiodtrns.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                        Отменить
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Импортировать
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>