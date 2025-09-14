<!-- Сведения о поездке -->
<!-- resources/views/social-taxi-orders/create-components/trip-info.blade.php -->
<div class="bg-gray-50 p-4 rounded-lg mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Сведения о поездке</h2>

    <div class="space-y-4">
        <!-- Тип поездки -->
        <div>
            <label for="zena_type" class="block text-sm font-medium text-gray-700">Тип поездки *</label>
            @if($type == 1) {{-- Для соцтакси всегда 1 и только для чтения --}}
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
                <option value="1" {{ old('zena_type', 1) == '1' ? 'selected' : '' }}>Поездка в одну сторону</option>
                <option value="2" {{ old('zena_type') == '2' ? 'selected' : '' }}>Поездка в обе стороны</option>
            </select>
            @error('zena_type')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">Выберите тип поездки: в одну или обе стороны</p>
            @endif
        </div>


        <div>
            <label for="visit_data" class="block text-sm font-medium text-gray-700">Дата и время поездки *</label>
            <input type="datetime-local" name="visit_data" id="visit_data" 
                   value="{{ old('visit_data') }}"
                   min="{{ now()->addDay()->format('Y-m-d\TH:i') }}"
                   max="{{ now()->addMonths(6)->format('Y-m-d\TH:i') }}" 
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
                      placeholder="{{ ($type == 2 || $type == 3) && (old('zena_type', 1) == '2') ? 'Введите обратный адрес' : 'Поле недоступно для поездки в одну сторону' }}"
                      {{ ($type == 2 || $type == 3) && (old('zena_type', 1) == '1') ? 'readonly disabled' : '' }}
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 {{ ($type == 2 || $type == 3) && (old('zena_type', 1) == '1') ? 'bg-gray-100 cursor-not-allowed' : '' }}">{{ old('adres_obratno') }}</textarea>
            @error('adres_obratno')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            @if($type == 2 || $type == 3)
            <p class="mt-1 text-xs text-gray-500">
                Обратный адрес доступен только для поездки в обе стороны
            </p>
            @endif
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