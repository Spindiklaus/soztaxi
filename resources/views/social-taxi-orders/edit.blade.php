<x-app-layout>

    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('social-taxi-orders.edit-components.header')
            
            <form action="{{ route('social-taxi-orders.update', $order) }}" method="POST" class="bg-white shadow rounded-lg p-6">
                @csrf
                @method('PUT')
                
                @include('social-taxi-orders.edit-components.order-info')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        @include('social-taxi-orders.edit-components.client-info')
                        
                        @include('social-taxi-orders.edit-components.benefits')
                    </div>
                    
                    <div>
                        @include('social-taxi-orders.edit-components.trip-info')
                        
                        @include('social-taxi-orders.edit-components.actions')
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Модальное окно с поездками клиента -->
    @include('social-taxi-orders.show-components.client-trips-modal')

    <!-- JavaScript - подключаем по логическим блокам -->
    <!-- основная инициализация и обработчики событий-->
    @include('social-taxi-orders.edit-components.scripts.main')
    <!--работа с данными клиента-->
    @include('social-taxi-orders.edit-components.scripts.client-data')
    <!--работа с данными категории-->
    @include('social-taxi-orders.edit-components.scripts.category-data')
    <!--работа с дополнительными условиями-->
    @include('social-taxi-orders.edit-components.scripts.dopus-data')
    <!--расчеты и вспомогательные функции-->
    @include('social-taxi-orders.edit-components.scripts.calculations')
    <!--работа с информацией о поездках-->
    @include('social-taxi-orders.edit-components.scripts.trip-info')
    <!--работа с оплатой такси ГАЗ и ЛА-->
    @include('social-taxi-orders.edit-components.scripts.taxi-type')
    <!--история поездок-->
    @include('social-taxi-orders.edit-components.scripts.trip-history-scripts')
   
    <!-- JavaScript для модального окна (ПОДКЛЮЧАЕМ ПОСЛЕ основного скрипта) -->
    @include('social-taxi-orders.show-components.scripts.modal-scripts')
    @include('social-taxi-orders.create-components.trip-history-modal')
    
    </x-app-layout>