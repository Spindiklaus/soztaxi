<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Редактировать Оператора такси</h1>

            <form action="{{ route('taxis.update', $taxi) }}" method="POST" class="bg-white shadow rounded-lg p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Две колонки -->
                <div class="grid grid-cols-1 gap-6">

                    <!-- Левая часть -->
                    <div class="space-y-4">
                        <!-- Название -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Оператор такси (наименование)</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $taxi->name) }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <!-- Коэффициенты -->
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label for="koef" class="block text-sm font-medium text-gray-700">Стоимость 1 км пути</label>
                                <input type="number" step="any" name="koef" id="koef"
                                       value="{{ old('koef', $taxi->koef) }}" required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="posadka" class="block text-sm font-medium text-gray-700">Стоимость посадки</label>
                                <input type="number" step="any" name="posadka" id="posadka"
                                       value="{{ old('posadka', $taxi->posadka) }}" required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <!-- Скидки -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="koef50" class="block text-sm font-medium text-gray-700">Стоимость 1 км пути (при 50% скидке)</label>
                                <input type="number" step="any" name="koef50" id="koef50"
                                       value="{{ old('koef50', $taxi->koef50) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="posadka50" class="block text-sm font-medium text-gray-700">Посадка (при 50% скидке)</label>
                                <input type="number" step="any" name="posadka50" id="posadka50"
                                       value="{{ old('posadka50', $taxi->posadka50) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Правая часть -->
                    <div class="space-y-4">
                        <!-- Авто и ГАЗель -->
                        <div class="grid grid-cols-2 gap-6 py-4">
                            <div>
                                <label for="zena1_auto" class="block text-sm font-medium text-gray-700">Легковой авто (один конец)</label>
                                <input type="number" step="any" name="zena1_auto" id="zena1_auto"
                                       value="{{ old('zena1_auto', $taxi->zena1_auto) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="zena2_auto" class="block text-sm font-medium text-gray-700">Легковой авто (туда-обратно)</label>
                                <input type="number" step="any" name="zena2_auto" id="zena2_auto"
                                       value="{{ old('zena2_auto', $taxi->zena2_auto) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6 py-4">
                            <div>
                                <label for="zena1_gaz" class="block text-sm font-medium text-gray-700">Стоимость оплаты ГАЗель (при поездке в один конец)</label>
                                <input type="number" step="any" name="zena1_gaz" id="zena1_gaz"
                                       value="{{ old('zena1_gaz', $taxi->zena1_gaz) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="zena2_gaz" class="block text-sm font-medium text-gray-700">Стоимость оплаты ГАЗель (при поездке в обе стороны)</label>
                                <input type="number" step="any" name="zena2_gaz" id="zena2_gaz"
                                       value="{{ old('zena2_gaz', $taxi->zena2_gaz) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <!-- Комментарий -->
                        <div>
                            <label for="komment" class="block text-sm font-medium text-gray-700">Комментарии</label>
                            <textarea name="komment" id="komment" rows="2"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('komment', $taxi->komment) }}</textarea>
                        </div>
                    </div>

                </div>

                <!-- Кнопки -->
                <div class="flex justify-end space-x-2 pt-6">
                    <a href="{{ route('taxis.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                        Отменить
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Сохранить изменения
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>