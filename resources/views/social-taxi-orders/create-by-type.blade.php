<!-- resources/views/social-taxi-orders/create.blade.php -->
<x-app-layout>

    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Заголовок -->
            @include('social-taxi-orders.create-components.header')
            
            <!-- Форма создания заказа -->
            <form action="{{ route('social-taxi-orders.store.by-type', $type) }}" method="POST" class="bg-white shadow rounded-lg p-6">
                @csrf
                
                <!-- Предварительная информация о заказе -->
                @include('social-taxi-orders.create-components.order-info')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Левая колонка -->
                    <div>
                        <!-- Сведения о клиенте -->
                        @include('social-taxi-orders.create-components.client-info')
                        
                        <!-- Льготы по поездке -->
                        @include('social-taxi-orders.create-components.benefits')
                    </div>
                    
                    <!-- Правая колонка -->
                    <div>
                        <!-- Сведения о поездке -->
                        @include('social-taxi-orders.create-components.trip-info')
                        
                        <!-- Кнопки действия -->
                        @include('social-taxi-orders.create-components.actions')
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Модальное окно с поездками клиента -->
    @include('social-taxi-orders.show-components.client-trips-modal')

    <!-- JavaScript -->
    @include('social-taxi-orders.create-components.scripts')
   
    <!-- JavaScript для модального окна (ПОДКЛЮЧАЕМ ПОСЛЕ основного скрипта) -->
    @include('social-taxi-orders.show-components.modal-scripts')
</x-app-layout>
