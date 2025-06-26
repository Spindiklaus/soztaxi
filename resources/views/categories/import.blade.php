<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Импорт категорий из CSV</h1>
            <!-- Описание -->
            <div class="text-sm text-gray-600">
                <p class="mt-1">Формат: <code>name;nmv;skidka;kol_p;is_soz;is_auto;is_gaz;kat_dop</code></p>
                <p class="mt-1">Пример:</p>
                <pre class="bg-gray-100 p-2 rounded mt-1 text-xs">
name;nmv;skidka;kol_p;is_soz;is_auto;is_gaz;kat_dop
Инвалид 1 группы;20280;100;10;1;0;0;1
Инвалид II степени;20290;50;10;1;0;0;2
                </pre>
            </div>

            <!-- Сообщение об успехе -->
            @if(session('success_count'))
            <x-alert type="success" title="Импорт завершён">
                {{ session('success_count') }} записей успешно импортировано.
            </x-alert>
            @endif
            @if(session('import_errors'))
            <x-alert type="error" title="Ошибки при импорте">
                <strong class="font-bold">Ошибки импорта:</strong>
                <ul class="mt-2 list-disc pl-5">
                    @foreach(session('import_errors') as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
            @endif

            <form action="{{ route('import.categories.process') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="csv_file" class="block text-sm font-medium text-gray-700">Выберите CSV-файл</label>
                    <input type="file" name="csv_file" id="csv_file" required accept=".csv,.txt"
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <div class="flex justify-end space-x-2">
                    <a href="{{ route('categories.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                        Отмена
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Загрузить
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>