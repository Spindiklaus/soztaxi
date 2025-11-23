<x-app-layout>
    <div class="bg-gray-100 py-2">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(isset($order))
                <!-- Заголовок и кнопки -->
                @include('social-taxi-orders.show-components.header')

                <!-- Вкладки -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <!-- Навигация по вкладкам -->
                    @include('social-taxi-orders.show-components.tabs-nav')

                    <div class="p-6">
                        <!-- Вкладка "Прием заказа" -->
                        @include('social-taxi-orders.show-components.reception-tab')

                        <!-- Вкладка "Работа с такси" -->
                        @include('social-taxi-orders.show-components.taxi-work-tab')

                        <!-- Вкладка "Закрытие (отмена) заказа" -->
                        @include('social-taxi-orders.show-components.closure-tab')
                    </div>
                </div>
                
                <!-- Модальное окно с поездками клиента -->
                @include('social-taxi-orders.show-components.client-trips-modal')
                
            @else
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="text-center text-red-600">
                        <h2 class="text-xl font-bold mb-2">Ошибка</h2>
                        <p>Заказ не найден</p>
                        <a href="{{ route('social-taxi-orders.index') }}" 
                           class="inline-block mt-4 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                            Вернуться к списку заказов
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- JavaScript для переключения вкладок -->
    @include('social-taxi-orders.show-components.scripts.tabs-script')
    <!-- JavaScript для модального окна -->
    @include('social-taxi-orders.show-components.scripts.modal-scripts')
</x-app-layout>