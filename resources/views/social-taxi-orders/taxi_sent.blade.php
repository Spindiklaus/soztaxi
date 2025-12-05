<!-- resources/views/social-taxi-orders/taxi_sent.blade.php -->
<x-app-layout>
    <div class="bg-gray-100 py-2">
        <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Заголовок -->
            <div class="flex justify-between items-center mb-2">
                <h1 class="text-2xl font-bold text-gray-800">
                    Переданные заказы в такси&nbsp;
                    <span class="text-lg font-normal text-gray-600">
                        (всего: {{ $totalOrders ?? $orders->total() }})
                    </span>
                </h1>
                
                <a href="{{ route('social-taxi-orders.index', $urlParams) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Все заказы
                </a>
<!--                 Экспорт в такси 
                <form id="export-form" action="{{-- route('taxi-orders.export.to.taxi') --}}" method="GET" target="_blank" class="inline">
                    <input type="hidden" name="date_from" value="{{-- request('date_from', date('Y-m-d')) --}}">
                    <input type="hidden" name="date_to" value="{{-- request('date_to', date('Y-m-d')) --}}">
                    <input type="hidden" name="sort" value="{{-- $sort ?? 'visit_data' --}}">
                    <input type="hidden" name="direction" value="{{-- $direction ?? 'asc' --}}">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition ease-in-out duration-150"
                        onclick="return confirm('Вы уверены, что хотите сформировать список для передачи в такси за выбранный период?')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        В такси (xls)
                    </button>
                </form>-->

                <!-- ОТменить передачу сведений в такси -->
                <form action="{{ route('taxi-orders.unset-sent-date') }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="date_from" value="{{ request('date_from', date('Y-m-d')) }}">
                    <input type="hidden" name="date_to" value="{{ request('date_to', date('Y-m-d')) }}">
                    <input type="hidden" name="taxi_id" value="{{ request('taxi_id') }}">
                    <input type="hidden" name="taxi_sent_at" value="{{ request('taxi_sent_at') }}">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition ease-in-out duration-150"
                        onclick="return confirm('Вы уверены, что хотите снять дату передачи в такси для всех заказов в выборке?')"
                        title="отменить статус заказа 'Передан в такси'"
                        >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Откат поездок 
                    </button>
                </form>
                
                <!-- Кнопка для загрузки файла Excel для сверки -->
                <form id="verify-excel-form" action="{{ route('taxi-orders.verify-excel') }}" method="POST" enctype="multipart/form-data" class="inline">
                    @csrf
                    <input type="file" name="excel_file" accept=".xlsx,.xls" required class="hidden" id="excel_file_input" onchange="this.form.submit();">
                    <input type="hidden" name="date_from" value="{{ request('date_from', date('Y-m-d')) }}">
                    <input type="hidden" name="date_to" value="{{ request('date_to', date('Y-m-d')) }}">
                    <label for="excel_file_input" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7m0 0l5-5m-5 5l5-5" />
                        </svg>
                        Сверить с такси
                    </label>
                </form>
                
                
                <!-- Кнопка "Перенести предварительные данные в фактические" -->
                 <form action="{{ route('taxi-orders.transfer.predictive.data') }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="date_from" value="{{ request('date_from', date('Y-m-d')) }}">
                    <input type="hidden" name="date_to" value="{{ request('date_to', date('Y-m-d')) }}">
                    <input type="hidden" name="taxi_id" value="{{ request('taxi_id') }}">
                    <input type="hidden" name="taxi_sent_at" value="{{ request('taxi_sent_at') }}">                        
                    <button type="submit"
                        title="перенос предварительных данных в фактические (только для соцтакси)"                            
                        class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition ease-in-out duration-150"
                        onclick="return confirm('Вы уверены, что хотите перенести предварительные данные в фактические для всех заказов соцтакси со статусом \'Передан в такси\' и заполненной предварительной дальностью?')">                        
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        Фактические данные
                    </button>
                </form>
 
            </div>
            
            @include('social-taxi-orders.taxi_sent-components.filters')
            
            <!-- Пагинация -->
            <div class="mt-2 mb-2">
                {{ $orders->links() }}
            </div>

            @include('social-taxi-orders.taxi_sent-components.table')

            <!-- Пагинация -->
            <div class="mt-2">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</x-app-layout>