<x-app-layout>
    <div class="container mx-auto py-8">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-800 leading-tight">Редактировать Группу: {{ $orderGroup->name }}</h2>
                <div class="mt-2">
                    <a href="{{ route('order-groups.index') }}" class="text-blue-600 hover:text-blue-900">&larr; Назад к списку</a>
                </div>
            </div>
            <div class="p-6">
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('order-groups.update', $orderGroup) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Название группы:</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $orderGroup->name) }}" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <!-- Добавьте другие поля для редактирования, если необходимо -->
                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Обновить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>