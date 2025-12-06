{{-- resources/views/fio_rips/importRIP.blade.php --}}

<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-green-600" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Импорт данных из Excel (ЗАГС)
                </h1>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <h2 class="text-lg font-semibold text-blue-800 mb-2">Инструкция по импорту:</h2>
                    <ul class="list-disc pl-5 text-sm text-blue-700 space-y-1">
                        <li>Загружаемый файл должен быть в формате Excel (.xlsx, .xls) или CSV</li>
                        <li>Файл должен содержать данные начиная с 7 строки (пропускаются заголовки)</li>
                        <li>Столбцы: ФИО, Дата рождения, Место рождения, Пол, Гражданство, Адрес, Дата смерти, Номер записи акта о смерти</li>
                        <li>Поля "ФИО" и "Пол" обязательны для импорта</li>
                        <li>Если поле "Пол" не равно "М" или "Ж", строка будет пропущена</li>
                        <li>Если номер записи акта о смерти уже существует в базе, строка будет пропущена</li>
                        <li>Серия и номер паспорта будут автоматически преобразованы из формата "36 15 176890" в "3615^176890"</li>
                    </ul>
                </div>

                @if(session('import_stats'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <h3 class="text-lg font-semibold text-green-800 mb-2">Результат импорта:</h3>
                        <ul class="list-disc pl-5 text-green-700 space-y-1">
                            <li>Всего строк обработано: {{ session('import_stats.total') }}</li>
                            <li>Успешно импортировано: {{ session('import_stats.imported') }}</li>
                            <li>Пропущено: {{ session('import_stats.skipped') }}</li>
                        </ul>
                        
                        @if(session('import_skipped_reasons') && count(session('import_skipped_reasons')) > 0)
                            <div class="mt-3">
                                <h4 class="font-medium text-green-800">Причины пропуска:</h4>
                                <ul class="list-disc pl-5 text-green-700 space-y-1">
                                    @foreach(session('import_skipped_reasons') as $reason)
                                        <li>{{ $reason }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif

                <form action="{{ route('import.fio_rips') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <div>
                        <label for="file" class="block text-sm font-medium text-gray-700 mb-2">
                            Выберите Excel-файл:
                        </label>
                        <div class="flex items-center">
                            <input type="file" name="file" id="file" 
                                   class="block w-full text-sm text-gray-500
                                   file:mr-4 file:py-2 file:px-4
                                   file:rounded-md file:border-0
                                   file:text-sm file:font-semibold
                                   file:bg-green-50 file:text-green-700
                                   hover:file:bg-green-100
                                   focus:outline-none"
                                   accept=".xlsx,.xls,.csv" required>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">
                            Поддерживаются форматы: XLSX, XLS, CSV
                        </p>
                    </div>

                    <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('fio_rips.index') }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Отмена
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Импортировать
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>