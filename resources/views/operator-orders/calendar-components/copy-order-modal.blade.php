<!-- Модальное окно для копирования заказа -->
    <div id="copy-order-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900">Копировать заказ СОЦТАКСИ</h3>
                <form id="copy-order-form" method="POST">
                    @csrf
                    <input type="hidden" id="copy-order-id" name="order_id">
                    <div class="mt-2 space-y-2">
                        <div>
                            <label for="copy-visit-date-time" class="block text-sm font-medium text-gray-700">Дата и время поездки</label>
                            <input type="datetime-local" id="copy-visit-date-time" name="visit_data" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="copy-direction" class="block text-sm font-medium text-gray-700">Направление</label>
                            <select id="copy-direction" name="type_kuda" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="1">Туда же</option>
                                <option value="2">Обратно</option>
                            </select>
                        </div>
                    </div>
                    <div class="items-center gap-2 mt-4">
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none">Создать копию</button>
                        <button type="button" onclick="closeCopyModal()" class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400 focus:outline-none">Отмена</button>
                    </div>
                </form>
            </div>
        </div>
    </div>