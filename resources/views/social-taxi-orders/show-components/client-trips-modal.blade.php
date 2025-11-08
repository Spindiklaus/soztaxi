<!-- Модальное окно с поездками клиента -->
<div id="client-trips-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg w-full max-w-6xl min-w-[300px] max-h-[90vh] overflow-hidden flex flex-col mx-auto">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 id="modal-title" class="text-lg font-semibold text-gray-800">Заказы</h3>
                <button onclick="closeClientTripsModal()" class="text-gray-400 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="overflow-y-auto flex-1">
                <div id="client-trips-content">
                    <!-- Здесь будут загружаться заказы -->
                    <div class="text-center py-8">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
                        <p class="mt-2 text-gray-600">Загрузка заказов...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>