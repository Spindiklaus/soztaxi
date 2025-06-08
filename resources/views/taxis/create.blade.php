<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Создать оператора такси</h1>

            <form action="{{ route('taxis.store') }}" method="POST" class="bg-white shadow rounded-lg p-6 space-y-6">
                @csrf

                <!-- Название -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Название</label>
                    <input type="text" name="name" id="name" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <!-- Статус life -->
                <div>
                    <label for="life" class="block text-sm font-medium text-gray-700">Статус</label>
                    <select name="life" id="life" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="1">Активен</option>
                        <option value="0">Не активен</option>
                    </select>
                </div>
                </div>
                <!-- Стоимость поездки -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="koef" class="block text-sm font-medium text-gray-700">Стоимость 1 км пути</label>
                        <input type="number" step="any" name="koef" id="koef" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="posadka" class="block text-sm font-medium text-gray-700">Стоимость посадка</label>
                        <input type="number" step="any" name="posadka" id="posadka" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Скидки -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="koef50" class="block text-sm font-medium text-gray-700">Стоимость 1 км пути (при 50% скидке)</label>
                        <input type="number" step="any" name="koef50" id="koef50"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="posadka50" class="block text-sm font-medium text-gray-700">Стоимость посадки (при 50% скидке)</label>
                        <input type="number" step="any" name="posadka50" id="posadka50"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Цены авто/газель -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="zena1_auto" class="block text-sm font-medium text-gray-700">Цена легкового авто (при поездке в одну сторону)</label>
                        <input type="number" step="any" name="zena1_auto" id="zena1_auto"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="zena2_auto" class="block text-sm font-medium text-gray-700">Цена легкового авто (при поездке туда-обратно)</label>
                        <input type="number" step="any" name="zena2_auto" id="zena2_auto"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="zena1_gaz" class="block text-sm font-medium text-gray-700">Цена ГАЗели (при поездке в одну сторону)</label>
                        <input type="number" step="any" name="zena1_gaz" id="zena1_gaz"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="zena2_gaz" class="block text-sm font-medium text-gray-700">Цена ГАЗели (при поездке в обе стороны)</label>
                        <input type="number" step="any" name="zena2_gaz" id="zena2_gaz"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                

                <!-- Комментарий -->
                <div>
                    <label for="komment" class="block text-sm font-medium text-gray-700">Комментарии</label>
                    <textarea name="komment" id="komment" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>

                <!-- Кнопки -->
                <div class="flex justify-end space-x-2 pt-6">
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