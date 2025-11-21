<!-- resources/views/order-groups/edit-components/add-order-modal.blade.php -->

<!-- Модальное окно для добавления заказа -->
    <div id="add-order-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <!-- Центрируем контейнер модального окна -->
        <div class="flex items-center justify-center min-h-full p-4">
            <div class="relative w-full max-w-2xl mx-auto border shadow-lg rounded-md bg-white"> <!-- max-w-2xl (~48rem или ~768px) -->
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="mt-3 sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg font-medium text-gray-900">Добавить заказ в группу</h3>
                        <form id="add-order-form" method="POST">
                            @csrf
                            @method('POST')
                            <input type="hidden" id="modal-order-group-id" name="order_group_id">
                            <div class="mt-2 space-y-2">
                                <div>
                                    <label for="modal-order-id" class="block text-sm font-medium text-gray-700">Выберите заказ</label>
                                    <select id="modal-order-id" name="order_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        <option value="">Загрузка...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="items-center gap-2 mt-4">
                                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none">Добавить</button>
                                <button type="button" onclick="closeAddOrderModal()" class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400 focus:outline-none">Отмена</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>