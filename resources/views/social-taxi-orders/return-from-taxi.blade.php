<!-- resources/views/social-taxi-orders/return-from-taxi.blade.php -->
<x-app-layout>
    <div class="bg-gray-100 py-2">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Заголовок -->
            <div class="flex justify-between items-center mb-2">
                <h1 class="text-3xl font-bold text-gray-800">
                    Возврат заказа из такси: 
                    <span class="{{ getOrderTypeColor($social_taxi_order->type_order) }} font-medium">
                        {{ getOrderTypeName($social_taxi_order->type_order) }}
                    </span>
                </h1>

                @php
                    $fromOperatorPage = session('from_operator_page');
                    $operatorCurrentType = session('operator_current_type');

                    $backRoute = route('social-taxi-orders.index', $urlParams ?? []);

                    if ($fromOperatorPage && $operatorCurrentType) {
                        $routeMap = [
                            1 => 'operator.social-taxi.index',
                            2 => 'operator.car.index',
                            3 => 'operator.gazelle.index'
                        ];

                        if (isset($routeMap[$operatorCurrentType])) {
                            $backRoute = route($routeMap[$operatorCurrentType], array_merge(['type_order' => $operatorCurrentType], $urlParams ?? []));
                        }
                    }
                @endphp

                <a href="{{ $backRoute }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Назад к списку
                </a>
            </div>

            <!-- Форма возврата заказа из такси -->
            <div class="bg-gray-200">
                <form action="{{ route('social-taxi-orders.return-from-taxi', array_merge(['social_taxi_order' => $social_taxi_order], request()->all())) }}" method="POST" class="bg-white shadow rounded-lg p-6">
                    @csrf
                    @method('PATCH')

                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">

                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-2">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Внимание!</strong> Вы собираетесь вернуть заказ из такси. 
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Информация о заказе -->
                    <div class="bg-gray-200 p-2 rounded-lg mb-2">
                        <h2 class="text-lg font-semibold text-gray-800 mb-2">Информация о заказе</h2>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Номер заказа</label>
                                <div class="mt-1 bg-gray-100 p-2 rounded-md font-medium">
                                    {{ $social_taxi_order->pz_nom }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Дата и время приема заказа</label>
                                <div class="mt-1 bg-gray-100 p-2 rounded-md font-medium">
                                    {{ $social_taxi_order->pz_data->format('d.m.Y H:i') }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Клиент</label>
                                <div class="mt-1 bg-gray-100 p-2 rounded-md font-medium">
                                    {{ $social_taxi_order->client->fio ?? 'Неизвестный клиент' }} (#{{ $social_taxi_order->client_id }})
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Дата и время поездки</label>
                                <div class="mt-1 bg-gray-100 p-2 rounded-md font-medium">
                                    {{ $social_taxi_order->visit_data ? $social_taxi_order->visit_data->format('d.m.Y H:i') : '-' }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Откуда ехать</label>
                                <div class="mt-1 bg-gray-100 p-2 rounded-md font-medium">
                                    {{ $social_taxi_order->adres_otkuda ?? '' }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Куда ехать</label>
                                <div class="mt-1 bg-gray-100 p-2 rounded-md font-medium">
                                    {{ $social_taxi_order->adres_kuda ?? '' }}
                                </div>
                            </div>
                            @if($social_taxi_order->adres_obratno)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Обратный адрес</label>
                                    <div class="mt-1 bg-gray-100 p-2 rounded-md font-medium">
                                        {{ $social_taxi_order->adres_obratno ?? '-' }}
                                    </div>
                                </div>
                            @endif
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Текущий статус</label>
                                <div class="mt-1">
                                    @if($social_taxi_order->currentStatus && $social_taxi_order->currentStatus->statusOrder)
                                        @php
                                            $status = $social_taxi_order->currentStatus->statusOrder;
                                            $colorClass = !empty($status->color) ? $status->color : 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                            {{ $status->name }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Нет статуса
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <!-- Информация о передаче в такси -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Передан в такси</label>
                                <div class="mt-1 bg-gray-100 p-2 rounded-md font-medium">
                                    {{ $social_taxi_order->taxi_sent_at->format('d.m.Y H:i') }} 
                                    (оператор {{ $social_taxi_order->taxi ? $social_taxi_order->taxi->name : '' }})
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Причина возврата -->
                    <div class="bg-white p-4 rounded-lg mb-2 border border-gray-200">
                        <div class="space-y-4">
                            <div>
                                <label for="reason" class="block text-sm font-medium text-gray-700">Причина возврата заказа из такси *</label>
                                <textarea name="reason" id="reason" 
                                          rows="2" 
                                          required
                                          placeholder="Укажите причину возврата заказа из такси"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">{{ old('reason') }}</textarea>
                                @error('reason')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Причина возврата обязательна для заполнения</p>
                            </div>

    <!--                        <div>
                                <label for="returned_at" class="block text-sm font-medium text-gray-700">Дата и время возврата *</label>
                                <input type="datetime-local" name="returned_at" id="returned_at" 
                                       value="{{ old('returned_at', now()->format('Y-m-d\TH:i')) }}"
                                       max="{{ now()->format('Y-m-d\TH:i') }}" 
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                                @error('returned_at')
                                    <p class="mt-1 text-sm text-red-600">{{-- $message --}}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">
                                    Дата возврата должна быть не позже текущей даты и времени.
                                </p>
                            </div>-->
                        </div>
                    </div>

                    <!-- Кнопки действия -->
                    <div class="flex justify-between items-center">
                        @php
                            $fromOperatorPage = session('from_operator_page');
                            $operatorCurrentType = session('operator_current_type');

                            if ($fromOperatorPage && $operatorCurrentType) {
                                $routeMap = [
                                    1 => 'operator.social-taxi.index',
                                    2 => 'operator.car.index',
                                    3 => 'operator.gazelle.index'
                                ];

                                if (isset($routeMap[$operatorCurrentType])) {
                                    $backRoute = route($routeMap[$operatorCurrentType], array_merge(['filter_type_order' => $operatorCurrentType], $urlParams ?? []));
                                } else {
                                    $backRoute = route('social-taxi-orders.index', $urlParams ?? []);
                                }
                            } else {
                                $backRoute = route('social-taxi-orders.index', $urlParams ?? []);
                            }
                        @endphp

                        <a href="{{ $backRoute }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Назад к списку
                        </a>

                        <div class="flex space-x-2">
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Вернуть заказ из такси
                            </button>
                        </div>
                    </div>
                </form>
            </div>    
        </div>
    </div>
</x-app-layout>