{{-- resources/views/order-groups/index.blade.php --}}
<x-app-layout>
    <div class="container mx-auto py-2 px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-2 border-b border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-800 leading-tight">Управление группами соцтакси</h2>
                <!-- Если разрешено создание вручную -->
                <!-- <a href="{{-- route('order-groups.create') --}}" class="ml-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Создать новую группу
                </a> -->
            </div>
            <div class="p-2">
                <!-- Включаем фильтры -->
                @include('order-groups.index-components.filters')
                 <div class="mt-6">
                    <!-- Обертка для прокрутки таблицы -->
                    <div class="rounded-lg border border-gray-200"> 
                        <div class="overflow-y-auto max-h-[70vh]">
                            @include('order-groups.index-components.table') <!-- Подключаем таблицу -->
                        </div>    
                    </div>
                </div>

                <div class="mt-4">
                    {{ $orderGroups->appends(request()->all())->links() }} <!-- Пагинация с сохранением параметров -->
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript для сортировки -->
    <script>
        function sortBy(field) {
            const currentSort = '{{ request("sort") }}';
            const currentDirection = '{{ request("direction") }}';
            let newDirection = 'asc';

            // Если поле сортировки то же самое, меняем направление
            if (currentSort === field) {
                newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
            }

            // Формируем URL с новыми параметрами сортировки
            const url = new URL(window.location);
            url.searchParams.set('sort', field);
            url.searchParams.set('direction', newDirection);
            // Сохраняем остальные параметры (фильтры)
            // Параметры фильтров уже будут в URL, если они были, благодаря request()->all()

            window.location.href = url.toString();
        }
    </script>
</x-app-layout>