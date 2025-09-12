<!-- Предварительная информация о заказе -->
<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Заголовок -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">
                    Создание нового заказа: 
                    <span class="{{ getOrderTypeColor($type) }} font-medium">
                        {{ getOrderTypeName($type) }}
                    </span>
                </h1>

                <a href="{{ route('social-taxi-orders.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Назад к списку
                </a>
            </div>

            <!-- Форма создания заказа -->
            <form action="{{ route('social-taxi-orders.store.by-type', $type) }}" method="POST" class="bg-white shadow rounded-lg p-6">
                @csrf

                <!-- Предварительная информация о заказе -->
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Номер заказа</label>
                            <div class="mt-1 bg-gray-100 p-2 rounded-md font-medium">
                                {{ $orderNumber }}
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Устанавливается автоматически</p>
                            <!-- Скрытое поле для передачи номера в форму -->
                            <input type="hidden" name="pz_nom" value="{{ $orderNumber }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Дата и время приема заказа</label>
                            <div class="mt-1 bg-gray-100 p-2 rounded-md font-medium">
                                {{ $orderDateTime->format('d.m.Y H:i:s') }}
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Устанавливается автоматически</p>
                            <!-- Скрытое поле для передачи даты в форму -->
                            <input type="hidden" name="pz_data" value="{{ $orderDateTime->format('Y-m-d H:i:s') }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Тип заказа</label>
                            <div class="mt-1 bg-gray-100 p-2 rounded-md font-medium {{ getOrderTypeColor($type) }}">
                                {{ getOrderTypeName($type) }}
                            </div>
                            <!-- Скрытое поле для передачи типа в форму -->
                            <input type="hidden" name="type_order" value="{{ $type }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Оператор</label>
                            <div class="mt-1 bg-gray-100 p-2 rounded-md font-medium">
                                {{ auth()->user()->name ?? 'Неизвестный оператор' }} ({{ auth()->user()->litera ?? 'UNK' }})
                            </div>
                            <!-- Скрытое поле для передачи ID оператора в форму -->
                            <input type="hidden" name="user_id" value="{{ auth()->id() ?? 1 }}">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Левая колонка -->
                    <div>
                        <!-- Сведения о клиенте -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Сведения о клиенте</h2>

                            <div class="space-y-4">
                                <div>
                                    <label for="client_id" class="block text-sm font-medium text-gray-700">Клиент *</label>
                                    <select name="client_id" id="client_id" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Выберите клиента</option>
                                        @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                            {{ $client->fio }} (#{{ $client->id }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('client_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="category_id" class="block text-sm font-medium text-gray-700">Категория инвалидности *</label>
                                    <select name="category_id" id="category_id" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Выберите категорию</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->nmv }} - {{ $category->name }} (Скидка: {{ $category->skidka }}%, Лимит: {{ $category->kol_p }} поездок/мес)
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Добавлены поля category_skidka и category_limit -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="category_skidka" class="block text-sm font-medium text-gray-700">Скидка по категории, %</label>
                                        <input type="number" name="category_skidka" id="category_skidka" 
                                               value="{{ old('category_skidka') }}"
                                               min="0" max="100" step="1"
                                               placeholder="Введите скидку по категории"
                                               readonly
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 cursor-not-allowed">
                                            @error('category_skidka')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                    </div>

                                    <div>
                                        <label for="category_limit" class="block text-sm font-medium text-gray-700">Лимит поездок по категории</label>
                                        <input type="number" name="category_limit" id="category_limit" 
                                               value="{{ old('category_limit') }}"
                                               min="10" max="26" step="1"
                                               placeholder="Введите лимит поездок по категории"
                                               readonly
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 cursor-not-allowed">
                                            @error('category_limit')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                    </div>
                                </div>

                                <div>
                                    <label for="client_tel" class="block text-sm font-medium text-gray-700">Телефон для связи</label>
                                    <input type="text" name="client_tel" id="client_tel" 
                                           value="{{ old('client_tel') }}"
                                           placeholder="Введите телефон клиента"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('client_tel')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                </div>

                                <div>
                                    <label for="client_invalid" class="block text-sm font-medium text-gray-700">Удостоверение инвалида</label>
                                    <input type="text" name="client_invalid" id="client_invalid" 
                                           value="{{ old('client_invalid') }}"
                                           placeholder="Введите номер удостоверения"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('client_invalid')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                </div>

                                <div>
                                    <label for="client_sopr" class="block text-sm font-medium text-gray-700">ФИО (сопровождающий)</label>
                                    <input type="text" name="client_sopr" id="client_sopr" 
                                           value="{{ old('client_sopr') }}"
                                           placeholder="Введите ФИО сопровождающего"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('client_sopr')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Льготы по поездке -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Дополнительные льготы по поездке</h2>

                            <div class="space-y-4">
                                <div>
                                    <label for="dopus_id" class="block text-sm font-medium text-gray-700">Дополнительные условия для скидок</label>
                                    <select name="dopus_id" id="dopus_id"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Выберите дополнительные условия</option>
                                        @foreach($dopusConditions as $dopus)
                                        <option value="{{ $dopus->id }}" {{ old('dopus_id') == $dopus->id ? 'selected' : '' }}>
                                            {{ $dopus->name }} (Скидка: {{ $dopus->skidka }}%, Лимит: {{ $dopus->kol_p }} поездок/мес)
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('dopus_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="skidka_dop_all" class="block text-sm font-medium text-gray-700">Окончательная скидка инвалиду, %</label>
                                        <input type="number" name="skidka_dop_all" id="skidka_dop_all" 
                                               value="{{ old('skidka_dop_all') }}"
                                               min="50" max="100" step="1"
                                               placeholder="Введите окончательную скидку"
                                               readonly
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 cursor-not-allowed">
                                            @error('skidka_dop_all')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                    </div>

                                    <div>
                                        <label for="kol_p_limit" class="block text-sm font-medium text-gray-700">Окончательный лимит поездок</label>
                                        <input type="number" name="kol_p_limit" id="kol_p_limit" 
                                               value="{{ old('kol_p_limit') }}"
                                               min="10" max="26" step="1"
                                               placeholder="Введите окончательный лимит поездок"
                                               readonly
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 cursor-not-allowed">
                                            @error('kol_p_limit')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                    </div>
                                </div>    
                            </div>
                        </div>
                    </div>

                    <!-- Правая колонка -->
                    <div>
                        <!-- Сведения о поездке -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Сведения о поездке</h2>

                            <div class="space-y-4">
                                <div>
                                    <label for="visit_data" class="block text-sm font-medium text-gray-700">Дата и время поездки *</label>
                                    <input type="datetime-local" name="visit_data" id="visit_data" 
                                           value="{{ old('visit_data') }}"
                                           min="{{ now()->addDay()->format('Y-m-d\TH:i') }}"
                                           max="{{ now()->addMonths(6)->format('Y-m-d\TH:i') }}" 
                                           step="300" 
                                           required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('visit_data')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                        <p class="mt-1 text-xs text-gray-500">
                                            Время указывается с шагом 5 минут.
                                            Дата поездки должна быть не раньше завтра ({{ now()->addDay()->format('d.m.Y') }}) 
                                            и не позже чем через полгода ({{ now()->addMonths(6)->format('d.m.Y') }})
                                        </p>
                                </div>

                                <!-- обязательный выбор оператора такси -->
                                <div>
                                    <label for="taxi_id" class="block text-sm font-medium text-gray-700">Оператор такси *</label>
                                    <select name="taxi_id" id="taxi_id" 
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Выберите оператора такси</option>
                                        @foreach($taxis as $taxi)
                                        <option value="{{ $taxi->id }}" {{ (old('taxi_id', $defaultTaxiId) == $taxi->id) ? 'selected' : '' }}>
                                            {{ $taxi->name }} (#{{ $taxi->id }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('taxi_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">Выбор оператора такси обязателен для сохранения заказа</p>
                                </div>
                                <div>
                                    <label for="adres_otkuda" class="block text-sm font-medium text-gray-700">Откуда ехать *</label>
                                    <textarea name="adres_otkuda" id="adres_otkuda" 
                                              rows="3" 
                                              required
                                              placeholder="Введите адрес отправки"
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('adres_otkuda') }}</textarea>
                                    @error('adres_otkuda')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="adres_kuda" class="block text-sm font-medium text-gray-700">Куда ехать *</label>
                                    <textarea name="adres_kuda" id="adres_kuda" 
                                              rows="3" 
                                              required
                                              placeholder="Введите адрес назначения"
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('adres_kuda') }}</textarea>
                                    @error('adres_kuda')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Обратный адрес (показываем только для типов 2 и 3 - легковое авто и ГАЗель) -->
                                @if($type != 1)
                                <div>
                                    <label for="adres_obratno" class="block text-sm font-medium text-gray-700">Обратный адрес</label>
                                    <textarea name="adres_obratno" id="adres_obratno" 
                                              rows="3" 
                                              placeholder="Введите обратный адрес (если есть)"
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('adres_obratno') }}</textarea>
                                    @error('adres_obratno')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">Обратный адрес используется только для легкового авто и ГАЗели</p>
                                </div>
                                @else
                                <!-- Скрытое поле для соцтакси -->
                                <input type="hidden" name="adres_obratno" value="">
                                    <!-- Предварительная дальность поездки -->
                                    <div>
                                        <label for="predv_way" class="block text-sm font-medium text-gray-700">Предварительная дальность поездки, км</label>
                                        <input type="number" name="predv_way" id="predv_way" 
                                               value="{{ old('predv_way') }}"
                                               min="0" 
                                               step="0.1"
                                               placeholder="Введите предварительную дальность поездки"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error('predv_way')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                            <p class="mt-1 text-xs text-gray-500">Предварительная дальность поездки в километрах</p>
                                    </div>    
                                    @endif
                            </div>
                        </div>

                        <!-- Кнопки действия -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('social-taxi-orders.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                                Отмена
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Создать заказ
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    @include('social-taxi-orders.create-components.scripts')
</x-app-layout>