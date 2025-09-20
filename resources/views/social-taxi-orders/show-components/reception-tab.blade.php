<div id="reception" class="tab-content active">
    <div class="grid">
        <!-- Сведения о заказе -->
        <div>

            <!-- Клиент -->
            <div class="border border-gray-200 rounded-lg mb-6">
                <div class="bg-gray-50 px-4 py-3 rounded-t-lg">
                    <button type="button" 
                            onclick="toggleClientInfo()"
                            class="flex items-center justify-between w-full text-left">
                        <h2 class="text-lg font-semibold text-gray-800">
                            Клиент: {{ $order->client->fio ?? 'Не указан' }}
                        </h2>
                        <svg id="client-info-arrow" class="h-5 w-5 transform transition-transform text-gray-500" 
                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div id="client-info-content" class="p-4 hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Удостоверение инвалида</label>
                            <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ $order->client_invalid ?? 'Не указано' }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Серия и номер паспорта</label>
                            <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ $order->kl_id ?? 'Не указан' }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Телефон для связи</label>
                            <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ $order->client_tel ?? 'Не указан' }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">ФИО (сопровождающий)</label>
                            <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ $order->client_sopr ?? 'Не указан' }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Категория инвалидности (NMV)</label>
                            <div class="mt-1 bg-gray-100 p-2 rounded-md">
                                {{ $order->category ? $order->category->nmv : 'Не указана' }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Скидка по категории, %</label>
                            <div class="mt-1 bg-gray-100 p-2 rounded-md">
                                {{ $order->category ? $order->category->skidka . '%' : 'Не указана' }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Лимит поездок для категории</label>
                            <div class="mt-1 bg-gray-100 p-2 rounded-md">
                                {{ $order->category ? $order->category->kol_p . ' поездок/мес' : 'Не указан' }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Дополнительные условия</label>
                            <div class="mt-1 bg-gray-100 p-2 rounded-md">
                                {{ $order->dopus ? $order->dopus->name : 'Не указаны' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Сведения о поездке -->
            <div class="border border-gray-200 rounded-lg mb-6">
                <div class="bg-gray-50 px-4 py-3 rounded-t-lg">
                    <button type="button" 
                            onclick="toggleTripInfo()"
                            class="flex items-center justify-between w-full text-left">
                        <h2 class="text-lg font-semibold text-gray-800">
                            @if($order->zena_type == 1)
                            <span>Поездка в одну сторону:</span>
                            @elseif($order->zena_type == 2)
                            <span>Поездка в ОБЕ стороны:</span>
                            @endif    
                            <span class="inline-flex items-center ml-2">
                                @if($order->visit_data)
                                <span class="inline-flex items-center px-2 py-1 rounded-l text-sm font-medium bg-blue-100 text-blue-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ $order->visit_data->format('d.m.Y') }}
                                </span>
                                <span class="inline-flex items-center px-2 py-1 rounded-r text-sm font-medium bg-blue-50 text-blue-700 border-l border-blue-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $order->visit_data->format('H:i') }}
                                </span>
                                @if($order->zena_type == 2 && $order->visit_obratno)
                                -
                                <span class="inline-flex items-center px-2 py-1 rounded-r text-sm font-medium bg-green-50 text-green-700 border-l border-green-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $order->visit_obratno->format('H:i') }}
                                </span>
                                @endif
                                @else
                                <span class="inline-flex items-center px-2 py-1 rounded text-sm font-medium bg-gray-100 text-gray-800">
                                    Не указана
                                </span>
                                @endif
                            </span>    
                        </h2>
                        <svg id="trip-info-arrow" class="h-5 w-5 transform transition-transform text-gray-500" 
                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>


                <div id="trip-info-content" class="p-4 hidden">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Откуда ехать</label>
                            <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ $order->adres_otkuda ?? 'Не указано' }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Куда ехать</label>
                            <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ $order->adres_kuda ?? 'Не указано' }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Обратный адрес</label>
                            <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ $order->adres_obratno ?? ' - ' }}</div>
                        </div>
                     </div>

                </div>
            </div>

            <!-- Сведения о заказе -->
            <div class="bg-gray-50 rounded-lg mb-6">
                <!-- Заголовок с кнопкой раскрытия -->
                <div class="px-4 py-3 bg-gray-100 rounded-t-lg border-b border-gray-200">
                    <button type="button" 
                            onclick="toggleBenefits()"
                            class="flex items-center justify-between w-full text-left">
                        <h2 class="text-lg font-semibold text-gray-800">
                            <span class="text-lg">
                                <span class="font-medium text-gray-700">Скидка </span>
                                <span class="text-red-600 font-semibold">{{ $order->skidka_dop_all . '%' ?? '0%' }}</span>
                            </span>

                            <span class="text-lg">
                                <span class="font-medium text-gray-700">лимит </span>
                                <span class="text-red-600 font-semibold">{{$order->kol_p_limit ?? '0' }} поездок/мес</span>
                            </span>

                            @if($order->category && $order->category->kat_dop)
                            <span class="text-lg">
                                <span class="font-medium text-gray-700">категория скидок:</span>
                                <span class="text-blue-600 font-semibold">{{ $order->category->kat_dop }}</span>
                            </span>
                            @endif

                            @if($order->category)
                            <span class="text-lg">
                                <span class="font-medium text-gray-700">(NMV:</span>
                                <span class="text-gray-600 font-semibold">{{ $order->category->nmv }})</span>
                            </span>
                            @endif
                        </h2>
                        <svg id="benefits-arrow" class="h-5 w-5 transform transition-transform text-gray-500" 
                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <!-- Содержимое льгот (скрыто по умолчанию) -->
                <div id="benefits-content" class="p-4 hidden">
                    <div class="flex flex-wrap items-center gap-2 md:gap-4">

                    </div>
                    <!-- Количество поездок клиента -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <div class="flex items-center">
                            <span class="text-lg font-semibold text-gray-800">Количество поездок клиента в этом месяце:</span>
                            <button 
                                onclick="showClientTrips({{ $order->client_id }}, '{{ $order->visit_data ? $order->visit_data->format('Y-m') : date('Y-m') }}')"
                                class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                                {{ getClientTripsCountInMonthByVisitDate($order->client_id, $order->visit_data) }}
                            </button>
                        </div>

                        <!-- Число фактических поездок в месяц -->
                        <div class="flex items-center mt-2">
                            <span class="text-lg font-semibold text-gray-800">Число фактических поездок в месяц:</span>
                            <button 
                                onclick="showClientActualTrips({{ $order->client_id }}, '{{ $order->visit_data ? $order->visit_data->format('Y-m') : date('Y-m') }}')"
                                class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-800 hover:bg-green-200 transition-colors">
                                {{ getClientActualTripsCountInMonthByVisitDate($order->client_id, $order->visit_data) }}
                            </button>
                        </div>

                        <!-- Число поездок переданных в такси -->
                        <div class="flex items-center mt-2">
                            <span class="text-lg font-semibold text-gray-800">Число поездок, переданных оператору такси:</span>
                            <button 
                                onclick="showClientTaxiSentTrips({{ $order->client_id }}, '{{ $order->visit_data ? $order->visit_data->format('Y-m') : date('Y-m') }}')"
                                class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-yellow-100 text-yellow-800 hover:bg-yellow-200 transition-colors">
                                {{ getClientTaxiSentTripsCountInMonthByVisitDate($order->client_id, $order->visit_data) }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>



        </div>   
    </div>
    <!-- Предварительный расчет -->
    @if($order->type_order == 1) 
    <div class="col-span-1">
        <div class="bg-gray-50 rounded-lg mb-6">
            <!-- Заголовок с кнопкой раскрытия -->
            <div class="px-4 py-3 bg-gray-100 rounded-t-lg border-b border-gray-200">
                <button type="button" 
                        onclick="toggleCalculation()"
                        class="flex items-center justify-between w-full text-left">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Предварительный расчет:
                        <span class="text-sm font-medium text-gray-700">Дальность</span>
                        <span class="text-sm text-gray-900">{{  number_format($order->predv_way,3,',', ' ') ?? '0' }} км</span>
                    </h2>
                    <svg id="calculation-arrow" class="h-5 w-5 transform transition-transform text-gray-500" 
                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>


            <!-- Содержимое расчета (скрыто по умолчанию) -->
            <div id="calculation-content" class="p-4 hidden">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-700">Оператор такси</span>
                        <span class="text-sm text-gray-900">
                            @if($order->taxi)
                            {{ $order->taxi->name }} (#{{ $order->taxi->id }})
                            @else
                            {{ $order->taxi_id ? 'Оператор #' . $order->taxi_id : 'Не выбран' }}
                            @endif
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-700">Цена поездки полная, без учета посадки, руб.</span>
                        <span class="text-sm text-gray-900">{{ number_format(calculateFullTripPrice($order, 11, $taxi), 11, ',', ' ') }} руб.</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-700">Сумма к оплате, руб.</span>
                        <span class="text-sm text-blue-600 font-semibold">{{ number_format(calculateClientPaymentAmount($order, 11, $taxi), 11, ',', ' ') }} руб.</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-700">Сумма к возмещению, руб.</span>
                        <span class="text-sm text-orange-600 font-semibold">{{ number_format(calculateReimbursementAmount($order, 11, $taxi), 11, ',', ' ') }} руб.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>