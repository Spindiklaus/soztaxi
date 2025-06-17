<x-app-layout>
    <!-- Сообщение об ошибках -->
    @if(session('import_errors'))
    <x-alert type="error" title="Ошибки при импорте">
        <ul class="list-disc pl-5 space-y-1">
            @foreach(session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-alert>
    @endif

    <!-- Сообщение об успехе -->
    @if(session('success_count'))
    <x-alert type="success" title="Импорт завершён">
        {{ session('success_count') }} записей успешно импортировано.
    </x-alert>
    @endif

    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Импорт записей из CSV</h1>

            <!-- Форма загрузки файла -->
            <form action="{{ route('import.fio_rips.process') }}" method="POST" enctype="multipart/form-data"
                  class="bg-white shadow rounded-lg p-6 space-y-6">
                @csrf

                <!-- Описание -->
                <div class="text-sm text-gray-600">
                    <p>Загрузите CSV-файл для массового добавления записей.</p>
                    <p class="mt-1">Формат: <code>fio;kl_id;data_r;sex;adres;data_rip;nom_zap</code></p>
                    <p class="mt-1">Все даты должны быть в формате <strong>дд.мм.гггг</strong>.</p>
                    <p class="mt-1">Пример:</p>
                    <pre class="bg-gray-100 p-2 rounded mt-1 text-xs">
fio;kl_id;data_r;sex;adres;data_rip;nom_zap
Иванов Иван Иванович;3601^123456;01.01.1990;М;Россия, Москва, ул. Ленина, 1;10.01.2024;1234567890
Петрова Мария Сергеевна;;12.05.1985;Ж;Россия, Санкт-Петербург;15.05.2024;9876543210
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
                    <a href="{{ route('fio_rips.index') }}"
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