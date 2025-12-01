<!-- resources/views/admin/taxi-orders/upload.blade.php -->
<x-app-layout>
    <div class="max-w-4xl mx-auto px-4 py-6">
        <h1 class="text-xl font-semibold text-gray-800 mb-4">Загрузка изменённого файла от оператора</h1>

        <form action="{{ route('admin.taxi-orders.import') }}" method="POST" enctype="multipart/form-data" class="bg-white shadow rounded-lg p-6">
            @csrf

            <div class="mb-4">
                <label for="file" class="block text-sm font-medium text-gray-700">Выберите файл</label>
                <input type="file" name="file" id="file" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label for="original_file_path" class="block text-sm font-medium text-gray-700">Путь к оригинальному файлу</label>
                <input type="text" name="original_file_path" id="original_file_path" value="{{ old('original_file_path') }}" 
                       placeholder="imports/exported_20251130_1143.xlsx"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Загрузить и применить изменения
                </button>
            </div>
        </form>

        @if(session('changes'))
            <div class="mt-6 bg-green-50 p-4 rounded-md">
                <h2 class="text-lg font-medium text-green-800 mb-2">Применённые изменения:</h2>
                <ul class="list-disc list-inside space-y-1">
                    @foreach(session('changes') as $change)
                        <li class="text-sm text-green-700">
                            <strong>Строка {{ $change['row'] }} ({{ $change['pz_nom'] }}):</strong>
                            @foreach($change['changes'] as $detail)
                                {{ $detail }}
                            @endforeach
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</x-app-layout>