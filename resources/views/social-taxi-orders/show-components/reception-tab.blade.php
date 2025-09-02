<div id="reception" class="tab-content active">
    <div class="grid">
        <!-- Сведения о заказе -->
        <div>
            <div class="border border-gray-200 rounded-lg p-4 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Сведения о заказе</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Дата приема заказа</label>
                        <div class="mt-1 bg-gray-100 p-2 rounded-md">
                            {{ $order->pz_data ? $order->pz_data->format('d.m.Y H:i') : 'Не указана' }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Номер заказа</label>
                        <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ $order->pz_nom ?? 'Не указан' }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Оператор</label>
                        <div class="mt-1 bg-gray-100 p-2 rounded-md">
                            @if($order->user)
                                {{ $order->user->name }} (#{{ $order->user_id }})
                            @elseif($order->user_id)
                                #{{ $order->user_id }}
                            @else
                                Не указан
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Тип заказа</label>
                        <div class="mt-1 bg-gray-100 p-2 rounded-md {{ getOrderTypeColor($order->type_order) }} font-semibold">
                            {{ getOrderTypeName($order->type_order) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Клиент -->
            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Клиент</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">ФИО (справочно)</label>
                        <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ $order->client->fio ?? 'Не указан' }}</div>
                    </div>

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
                        <label class="block text-sm font-medium text-gray-700">Категория инвалидности</label>
                        <div class="mt-1 bg-gray-100 p-2 rounded-md">
                            {{ $order->category ? $order->category->nmv : 'Не указана' }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">NMV (код категории)</label>
                        <div class="mt-1 bg-gray-100 p-2 rounded-md">
                            {{ $order->category ? $order->category->nmv : 'Не указан' }}
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

            <!-- Сведения о поездке -->
            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Сведения о поездке</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Дата поездки</label>
                        <div class="mt-1 bg-gray-100 p-2 rounded-md">
                            {{ $order->visit_data ? $order->visit_data->format('d.m.Y H:i') : 'Не указана' }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Откуда ехать</label>
                        <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ $order->adres_otkuda ?? 'Не указано' }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Куда ехать</label>
                        <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ $order->adres_kuda ?? 'Не указано' }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Организация, связанная с поездкой (справочно)</label>
                        <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ $order->adres_obratno ?? 'Не указана' }}</div>
                    </div>
                </div>
            </div>
        </div>   
    </div>
    <!-- Предварительный расчет -->
    <div class="col-span-1">
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Предварительный расчет</h2>

            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm font-medium text-gray-700">Дальность, км</span>
                    <span class="text-sm text-gray-900">{{ $order->predv_way ?? '0' }} км</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-sm font-medium text-gray-700">Оператор такси</span>
                    <span class="text-sm text-gray-900">{{ $order->taxi_id ? 'Оператор #' . $order->taxi_id : 'Не выбран' }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-sm font-medium text-gray-700">Цена поездки полная, руб.</span>
                    <span class="text-sm text-gray-900">{{ $order->taxi_price ?? '0' }} руб.</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-sm font-medium text-gray-700">Сумма к оплате, руб.</span>
                    <span class="text-sm text-blue-600 font-semibold">{{ $order->taxi_price ?? '0' }} руб.</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-sm font-medium text-gray-700">Сумма к возмещению, руб.</span>
                    <span class="text-sm text-orange-600 font-semibold">{{ $order->taxi_price ?? '0' }} руб.</span>
                </div>
            </div>
        </div>

        <!-- Льготы по поездке -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Льготы по поездке</h2>

            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm font-medium text-gray-700">Скидка, %</span>
                    <span class="text-sm text-red-600 font-semibold">{{ $order->category ? $order->category->skidka . '%' : '0%' }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-sm font-medium text-gray-700">Лимит поездок</span>
                    <span class="text-sm text-red-600 font-semibold">{{ $order->category ? $order->category->kol_p : '0' }} поездок/мес</span>
                </div>
            </div>
        </div>
    </div>
</div>