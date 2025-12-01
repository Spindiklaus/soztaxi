<!-- resources/views/admin/taxi-orders/compare-upload.blade.php -->
<x-app-layout>
    <div class="max-w-4xl mx-auto px-4 py-6">
        <h1 class="text-xl font-semibold text-gray-800 mb-4">Сравнение файлов с такси</h1>

        <form action="{{ route('admin.taxi-orders.compare-and-import') }}" method="POST" enctype="multipart/form-data" class="bg-white shadow rounded-lg p-6">
            @csrf

            <div class="mb-4">
                <label for="original_file" class="block text-sm font-medium text-gray-700">Ваш файл (оригинальный)</label>
                <input type="file" name="original_file" id="original_file" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label for="taxi_file" class="block text-sm font-medium text-gray-700">Файл от такси (изменённый)</label>
                <input type="file" name="taxi_file" id="taxi_file" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="apply_changes" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Применить изменения к заказам</span>
                </label>
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Сравнить и импортировать
                </button>
            </div>
        </form>

        @if(session('changes'))
            <div class="mt-6 bg-blue-50 p-4 rounded-md">
                <h2 class="text-lg font-medium text-blue-800 mb-2">Обнаруженные изменения:</h2>
                <ul class="list-disc list-inside space-y-1">
                    @foreach(session('changes') as $change)
                        <li class="text-sm text-blue-700">
                            <strong>Строка {{ $change['row'] }} ({{ $change['pz_nom'] }}):</strong>
                            @foreach($change['changes'] as $detail)
                                {{ $detail }}<br>
                            @endforeach
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('errors'))
            <div class="mt-6 bg-red-50 p-4 rounded-md">
                <h2 class="text-lg font-medium text-red-800 mb-2">Ошибки:</h2>
                <ul class="list-disc list-inside space-y-1">
                    @foreach(session('errors') as $error)
                        <li class="text-sm text-red-700">
                            <strong>Строка {{ $error['row'] }}:</strong> {{ $error['message'] }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</x-app-layout>