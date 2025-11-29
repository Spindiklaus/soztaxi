<!-- Сведения о поездке -->
<!-- resources/views/social-taxi-orders/edit-components/trip-info.blade.php -->
<div class="bg-gray-50 p-4 rounded-lg mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Сведения о поездке</h2>

    <div class="space-y-4">
        <!-- Тип поездки -->
        <div>
            <label for="zena_type" class="block text-sm font-medium text-gray-700">Тип поездки *</label>
            @if($order->type_order == 1) {{-- Для соцтакси (type_order == 1) zena_type = 1, только для чтения --}}
                <select name="zena_type" id="zena_type" 
                        readonly disabled
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 cursor-not-allowed">
                    <option value="1" selected>Поездка в одну сторону</option>
                </select>
                <input type="hidden" name="zena_type" value="1"> {{-- Скрытое поле для передачи значения --}}
                <p class="mt-1 text-xs text-gray-500">Для соцтакси тип поездки всегда "в одну сторону"</p>
            @else {{-- Для легкового авто и ГАЗели --}}
                <select name="zena_type" id="zena_type" 
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="1" {{ $order->zena_type == '1' ? 'selected' : '' }}>Поездка в одну сторону</option>
                    <option value="2" {{ $order->zena_type == '2' ? 'selected' : '' }}>Поездка в обе стороны</option>
                </select>
                @error('zena_type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Выберите тип поездки: в одну или обе стороны</p>
            @endif
        </div>


        <div>
            <label for="visit_data" class="block text-sm font-medium text-gray-700">Дата и время поездки *</label>
            @if($order->order_group_id)
                <input type="datetime-local" name="visit_data" id="visit_data" 
                       value="{{ old('visit_data', $order->visit_data) }}"
                       readonly disabled
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 cursor-not-allowed">
                <input type="hidden" name="visit_data" value="{{ old('visit_data', $order->visit_data) }}">
                <p class="mt-1 text-xs text-yellow-800 bg-yellow-50">
                    Дата и время поездки заблокированы, так как заказ в группе. 
                    Изменения возможны только при разгруппировке заказа.
                </p>
            @else
                <input type="datetime-local" name="visit_data" id="visit_data" 
                       value="{{ old('visit_data', $order->visit_data) }}"
                       required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('visit_data')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            @endif
        </div>

        <!-- обязательный выбор оператора такси -->
        <div>
            <label for="taxi_id" class="block text-sm font-medium text-gray-700">Оператор такси *</label>
            <select name="taxi_id" id="taxi_id" 
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Выберите оператора такси</option>
                @foreach($taxis as $taxi)
                <option value="{{ $taxi->id }}" {{ (old('taxi_id', $order->taxi_id) == $taxi->id) ? 'selected' : '' }}>
                    {{ $taxi->name }} (#{{ $taxi->id }})
                </option>
                @endforeach
            </select>
            @error('taxi_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="flex items-end space-x-2 mt-2">
              @if(!$order->order_group_id)
                <div class="flex-1">
                    <label for="adres_otkuda" class="block text-sm font-medium text-gray-700">Откуда ехать *</label>
                    <textarea name="adres_otkuda" id="adres_otkuda" 
                              rows="2" 
                              required
                              placeholder="Введите адрес отправки"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('adres_otkuda', $order->adres_otkuda) }}
                    </textarea>
                    @error('adres_otkuda')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <!-- Кнопка для выбора из истории адресов -->
                <button type="button" 
                        id="open-address-history-btn"
                        class="mt-6 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    История
                </button>
             @else <!-- заказ сугруппирован -->
                <textarea name="adres_otkuda" id="adres_otkuda" 
                              rows="2" 
                              readonly disabled
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 cursor-not-allowed focus:border-blue-500 focus:ring-blue-500">{{ old('adres_otkuda', $order->adres_otkuda) }}
                </textarea>
                <input type="hidden" name="adres_otkuda" value="{{ old('adres_otkuda', $order->adres_otkuda) }}">
                <p class="mt-1 text-xs text-yellow-800 bg-yellow-50">
                    Адрес поездки заблокирован, так как заказ в группе. 
                </p>
             @endif
        </div>
        <!-- Дополнительная информация об адресе "откуда" -->    
        <div class="mt-2">
            <label for="adres_otkuda_info" class="block text-sm font-medium text-gray-700">Дополнительная информация к адресу ОТКУДА</label>
            <textarea name="adres_otkuda_info" id="adres_otkuda_info" 
              rows="1" 
              placeholder="Телефон, особенности заезда и т.д."
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('adres_otkuda_info', $order->adres_otkuda_info) }}</textarea>
            @error('adres_otkuda_info')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">Укажите дополнительную информацию: телефон, особенности заезда и т.д.</p>
        </div>    

        <div>
            @if(!$order->order_group_id)
                <label for="adres_kuda" class="block text-sm font-medium text-gray-700">Куда ехать *</label>
                <textarea name="adres_kuda" id="adres_kuda" 
                      rows="2" 
                      required
                      placeholder="Введите адрес назначения"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{old('adres_kuda', $order->adres_kuda) }}</textarea>
                @error('adres_kuda')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            @else <!-- заказ сугруппирован -->
                <textarea name="adres_kuda" id="adres_kuda" 
                              rows="2" 
                              readonly disabled
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 cursor-not-allowed focus:border-blue-500 focus:ring-blue-500">{{ old('adres_kuda', $order->adres_kuda) }}
                </textarea>
                <input type="hidden" name="adres_kuda" value="{{ old('adres_kuda', $order->adres_kuda) }}">
                <p class="mt-1 text-xs text-yellow-800 bg-yellow-50">
                    Адрес КУДА заблокирован, так как заказ в группе. 
                </p>
             @endif    
        </div>
        <!-- Дополнительная информация об адресе "куда" -->
        <div class="mt-2">
            <label for="adres_kuda_info" class="block text-sm font-medium text-gray-700">Дополнительная информация к адресу КУДА</label>
            <textarea name="adres_kuda_info" id="adres_kuda_info" 
                  rows="1" 
                  placeholder="Телефон, особенности заезда и т.д."
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('adres_kuda_info', $order->adres_kuda_info) }}</textarea>
            @error('adres_kuda_info')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">Укажите дополнительную информацию: телефон, особенности заезда и т.д.</p>
        </div>

        <!-- Обратный адрес (показываем только для типов 2 и 3 - легковое авто и ГАЗель) -->
        @if($order->type_order != 1)
            <div>
                <label for="visit_obratno" class="block text-sm font-medium text-gray-700">Дата и время обратной поездки</label>

                <!-- Всегда создаем поле с id, но делаем его readonly/disabled в зависимости от zena_type -->
                <input type="datetime-local" 
                       name="visit_obratno" 
                       id="visit_obratno"
                       value="{{ old('visit_obratno', $order->visit_obratno) }}"
                       {{ $order->zena_type == '1' ? 'readonly disabled' : '' }}
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 {{ $order->zena_type == '1' ? 'bg-gray-100 cursor-not-allowed' : '' }}">

                @error('visit_obratno')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">
                    Время обратной поездки должно быть позже времени основной поездки
                </p>
            </div>
            <div>
                <label for="adres_obratno" class="block text-sm font-medium text-gray-700">Обратный адрес</label>
                <textarea name="adres_obratno" 
                          id="adres_obratno" 
                          rows="3" 
                          placeholder="{{ $order->zena_type == '2' ? 'Введите обратный адрес' : 'Поле недоступно для поездки в одну сторону' }}"
                          {{ $order->zena_type == '1' ? 'readonly disabled' : '' }}
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 
                    {{ $order->zena_type == '1' ? 'bg-gray-100 cursor-not-allowed' : '' }}">{{ old('adres_obratno', $order->adres_obratno) }}</textarea>
                @error('adres_obratno')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">
                    Обратный адрес доступен только при поездке в обе стороны
                </p>
            </div>

            <!-- Цена поездки -->
            <div>
                <label for="taxi_price" class="block text-sm font-medium text-gray-700">Цена поездки</label>
                <input type="number" name="taxi_price" id="taxi_price" 
                       value="{{ old('taxi_price', $order->taxi_price) }}"
                       step="0.01"
                       readonly
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 cursor-not-allowed focus:border-blue-500 focus:ring-blue-500">
                @error('taxi_price')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Сумма возмещения -->
            <div>
                <label for="taxi_vozm" class="block text-sm font-medium text-gray-700">Сумма возмещения</label>
                <input type="number" name="taxi_vozm" id="taxi_vozm" 
                       value="{{ old('taxi_vozm', $order->taxi_vozm) }}"
                       step="0.01"
                       readonly
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 cursor-not-allowed focus:border-blue-500 focus:ring-blue-500">
                @error('taxi_vozm')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @else
            <!-- Скрытое поле для соцтакси -->
            <input type="hidden" name="adres_obratno" value="">
            <!-- Предварительная дальность поездки -->
            <div>
                <label for="predv_way" class="block text-sm font-medium text-gray-700">Предварительная дальность поездки, км</label>
                <input type="number" name="predv_way" id="predv_way" 
                       value="{{  old('predv_way', $order->predv_way)}}"
                       min="0" 
                       step="0.1"
                       placeholder="Введите предварительную дальность поездки"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('predv_way')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Предварительная дальность поездки в километрах</p>
            </div>    
            <!-- Расчетные значения для соцтакси -->
            <div id="calculation-results" class="bg-blue-50 p-4 rounded-lg mt-4" style="display: none;">
                <h3 class="text-md font-semibold text-blue-800 mb-2">Предварительные значения:</h3>
                <div class="space-y-2">
                    <div>
                        <span class="font-medium">Цена поездки полная, без учета посадки:</span>
                        <span id="full-trip-price" class="ml-2 font-bold">0,00</span> руб.
                    </div>
                    <div>
                        <span class="font-medium">Сумма к оплате:</span>
                        <span id="client-payment-amount" class="ml-2 font-bold">0,00</span> руб.
                    </div>
                    <div>
                        <span class="font-medium">Сумма к возмещению:</span>
                        <span id="reimbursement-amount" class="ml-2 font-bold">0,00</span> руб.
                    </div>
                    <div id="taxi-info" class="text-sm text-gray-600 mt-2">
                        <span>Оператор такси: </span>
                        <span id="taxi-name" class="font-semibold"></span>
                    </div>
                </div>
            </div>
        @endif
    </div>
    
</div>