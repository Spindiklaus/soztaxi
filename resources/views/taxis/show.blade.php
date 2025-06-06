<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Просмотр сведений оператора такси</h1>

            <div class="bg-white shadow rounded-lg p-6">
                <!-- Название -->
                <div class="mb-4">
                    <strong>Оператор такси (название):</strong> {{ $taxi->name }}
                </div>

                <!-- Стоимость поездки -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div><strong>Стоимость 1 км пути:</strong> {{ $taxi->koef != 0 ? number_format($taxi->koef, 11) : '-' }}</div>
                    <div><strong>Посадка:</strong> {{ $taxi->posadka }}</div>
                </div>

                <!-- Скидки -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div><strong>Стоимость 1 км пути (при 50% скидке):</strong> {{ $taxi->koef50 }}</div>
                    <div><strong>Посадка (при 50% скидке):</strong> {{ $taxi->posadka50 }}</div>
                </div>

                <!-- Цены авто/газель -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div><strong>Цена легкового авто (в один конец):</strong> {{$taxi->zena1_auto != 0 ? number_format($taxi->zena1_auto, 11) : '-' }} </div>
                    <div><strong>Цена легкового авто (туда-обратно):</strong> {{$taxi->zena2_auto != 0 ? number_format($taxi->zena2_auto, 11) : '-' }}</div>
                    <div><strong>Цена ГАЗели (в один конец):</strong> {{ $taxi->zena1_gaz }}</div>
                    <div><strong>Цена ГАЗели (туда-обратно):</strong> {{ $taxi->zena2_gaz }}</div>
                </div>

                <!-- Комментарий -->
                <div class="mb-4">
                    <strong>Комментарии:</strong> {{ $taxi->komment ?: '-' }}
                </div>

                <!-- Кнопка редактирования -->
                <div class="flex justify-end">
                    <a href="{{ route('taxis.edit', $taxi) }}"
                       class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-md hover:bg-yellow-200">
                        Редактировать
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>