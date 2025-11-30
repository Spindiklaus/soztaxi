<!-- resources/views/operator-orders/calendar-components/copy-order-multiple-modal.blade.php -->

<!-- Модальное окно для множественного копирования заказа -->
<div id="copy-order-multiple-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border max-w-7xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900">Множественное копирование заказа СОЦТАКСИ</h3>
            <form id="copy-order-multiple-form" method="POST">
                @csrf
                @method('POST')
                <input type="hidden" id="copy-multiple-order-id" name="original_order_id"> <!-- ID оригинального заказа -->

                <div class="mt-2 space-y-2">
                    <!-- Информация об оригинальном заказе -->
                    <div class="bg-gray-50 p-2 rounded-md">
                        <p class="text-sm text-gray-700">
                            ID: <span id="original-order-info"></span>, 
                            дата: <span id="original-date-info"></span>, 
                            время: <span id="original-time-info"></span>,
                            откуда: <span id="original-from-info"></span>, 
                            куда: <span id="original-to-info"></span>,
                            предв. дальность: <span id="original-way-info"></span>
                        </p>
                    </div>

                    <!-- Календарь -->
                    <div id="copy-calendar-container">
                        <!-- Календарь будет сгенерирован динамически -->
                    </div>
                </div>
                <div class="items-center gap-2 mt-4">
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none">Создать копии</button>
                    <button type="button" onclick="closeCopyMultipleModal()" class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400 focus:outline-none">Отмена</button>
                </div>
            </form>
        </div>
    </div>
</div>