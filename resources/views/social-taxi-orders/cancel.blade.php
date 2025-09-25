<!-- resources/views/social-taxi-orders/cancel.blade.php -->
<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Заголовок -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">
                    Отмена заказа: 
                    <span class="{{ getOrderTypeColor($social_taxi_order->type_order) }} font-medium">
                        {{ getOrderTypeName($social_taxi_order->type_order) }}
                    </span>
                    №{{ $social_taxi_order->pz_nom }}
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

            <!-- Форма отмены заказа -->
            <form action="{{ route('social-taxi-orders.cancel', $social_taxi_order) }}" method="POST" class="bg-white shadow rounded-lg p-6">
                @csrf
                @method('PATCH')

                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                <strong>Внимание!</strong> Вы собираетесь отменить заказ. 
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Информация о заказе -->
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Информация о заказе</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Номер заказа</label>
                            <div class="mt-1 bg-gray-100 p-2 rounded-md font-medium">
                                {{ $social_taxi_order->pz_nom }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Дата и время приема заказа</label>
                            <div class="mt-1 bg-gray-100 p-2 rounded-md font-medium">
                                {{ $social_taxi_order->pz_data->format('d.m.Y H:i:s') }}
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
                                {{ $social_taxi_order->adres_otkuda ?? '-' }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Куда ехать</label>
                            <div class="mt-1 bg-gray-100 p-2 rounded-md font-medium">
                                {{ $social_taxi_order->adres_kuda ?? '-' }}
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
                    </div>
                </div>

                <!-- Причина отмены -->
                <div class="bg-white p-4 rounded-lg mb-6 border border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Причина отмены заказа</h2>

                    <div class="space-y-4">
                        <div>
                            <label for="reason" class="block text-sm font-medium text-gray-700">Причина отмены *</label>
                            <textarea name="reason" id="reason" 
                                      rows="4" 
                                      required
                                      placeholder="Укажите причину отмены заказа"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">{{ old('reason') }}</textarea>
                            @error('reason')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Причина отмены обязательна для заполнения</p>
                        </div>

                        <div>
                            <label for="cancelled_at" class="block text-sm font-medium text-gray-700">Дата и время отмены *</label>
                            <input type="datetime-local" name="cancelled_at" id="cancelled_at" 
                                   value="{{ old('cancelled_at', now()->format('Y-m-d\TH:i')) }}"
                                   max="{{ now()->format('Y-m-d\TH:i') }}" 
                                   required readonly
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                @error('cancelled_at')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">
                                    Дата отмены должна быть не позже даты и времени поездки
                                </p>
                        </div>
                    </div>
                </div>

                <!-- Кнопки действия -->
                <div class="flex justify-between items-center">
                    <a href="{{ route('social-taxi-orders.index', $urlParams ?? []) }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Назад к списку
                    </a>

                    <div class="flex space-x-2">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Отменить заказ
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>