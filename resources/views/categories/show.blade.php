<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow overflow-hidden rounded-lg">
                <div class="px-4 py-5 sm:p-6 space-y-4">
                    <h1 class="text-2xl font-bold text-gray-800 mb-6 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 inline align-middle text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                        </svg>
                        {{ $category->name }}
                    </h1>

                    <p class="text-sm text-gray-500">Текущая сортировка: {{request('sort') }} / {{ request('direction') }}</p>   
                    <!-- Данные категории -->
                    <div class="space-y-4">
                        <div class="flex justify-between border-b pb-2">
                            <dt class="text-sm font-medium text-gray-500">ID</dt>
                            <dd class="text-sm text-gray-900">{{ $category->id }}</dd>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <dt class="text-sm font-medium text-gray-500">NMV</dt>
                            <dd class="text-sm text-gray-900">{{ $category->nmv }}</dd>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <dt class="text-sm font-medium text-gray-500">Название</dt>
                            <dd class="text-sm text-gray-900">{{ $category->name }}</dd>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <dt class="text-sm font-medium text-gray-500">Скидка (%)</dt>
                            <dd class="text-sm text-gray-900">{{ $category->skidka }}%</dd>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <dt class="text-sm font-medium text-gray-500">Поездок в месяц</dt>
                            <dd class="text-sm text-gray-900">{{ $category->kol_p }}</dd>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <dt class="text-sm font-medium text-gray-500">Оператор</dt>
                            <dd class="text-sm text-gray-900">{{ $category->user?->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <dt class="text-sm font-medium text-gray-500">Соцтакси?</dt>
                            <dd class="text-sm text-gray-900">{{ $category->is_soz ? 'Да' : 'Нет' }}</dd>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <dt class="text-sm font-medium text-gray-500">Легковой авто?</dt>
                            <dd class="text-sm text-gray-900">{{ $category->is_auto ? 'Да' : 'Нет' }}</dd>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <dt class="text-sm font-medium text-gray-500">ГАЗель?</dt>
                            <dd class="text-sm text-gray-900">{{ $category->is_gaz ? 'Да' : 'Нет' }}</dd>
                        </div>

                        @if ($category->komment)
                            <div class="pt-4 border-t border-gray-200">
                                <dt class="text-sm font-medium text-gray-500">Комментарий</dt>
                                <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">
                                    {{ $category->komment }}
                                </dd>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Кнопки действий -->
                <div class="px-4 py-4 flex justify-end space-x-3">
                    <a href="{{ route('categories.edit', ['category' => $category, 'sort' => request('sort'), 'direction' => request('direction')]) }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-gray-500 rounded-md text-sm font-medium">
                        Редактировать
                    </a>
                    
                    <a href="{{ route('categories.index', ['sort' => request('sort'), 'direction' => request('direction')]) }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-gray-500 rounded-md text-sm font-medium">
                        Назад
                    </a>

                    <form action="{{ route('categories.destroy', ['category' => $category, 'sort' => request('sort'), 'direction' => request('direction')]) }}" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                onclick="return confirm('Вы уверены, что хотите удалить эту категорию?')"
                                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm font-medium">
                            Удалить
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>