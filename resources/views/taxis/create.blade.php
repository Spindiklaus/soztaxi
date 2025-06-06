<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Создать оператора такси</h1>
            <form action="{{ route('taxis.store') }}" method="POST" class="bg-white shadow rounded-lg p-6">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Название</label>
                    <input type="text" name="name" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500">
                </div>
                <div class="mb-4">
                    <label for="koef" class="block text-sm font-medium text-gray-700">Стоимость 1 км пути</label>
                    <input type="number" step="any" name="koef" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500">
                </div>
                <div class="mb-4">
                    <label for="posadka" class="block text-sm font-medium text-gray-700">Посадка</label>
                    <input type="number" step="any" name="posadka" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500">
                </div>
                <div class="mb-4">
                    <label for="komment" class="block text-sm font-medium text-gray-700">Комментарии</label>
                    <textarea name="komment" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500"></textarea>
                </div>
                <div class="flex justify-end space-x-2">
                    <a href="{{ route('taxis.index') }}"
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