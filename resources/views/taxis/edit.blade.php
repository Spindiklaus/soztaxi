<x-app-layout>
    <div class="bg-gray-50 py-6">
        <div class=" max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Добавляем отображение информации об изменениях -->
           <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Добавляем отображение информации об изменениях -->
            @if(session('price_update_info'))
                @php
                    $info = session('price_update_info');
                    // Получаем заказы для отображения дополнительной информации
                    $orderIds = array_keys($info['orders_before']);
                    $ordersDetails = \App\Models\Order::whereIn('id', $orderIds)->get()->keyBy('id');
                @endphp
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-medium text-yellow-800">Обновление цен по новым тарифам</h3>
                            <p class="text-yellow-700">Обновлено заказов: {{ $info['count'] }}</p>
                            
                            <div class="mt-4">
                                <h4 class="font-medium text-yellow-800">Детали изменений:</h4>
                                <div class="mt-2 max-h-60 overflow-y-auto">
                                    @foreach($info['orders_before'] as $orderId => $before)
                                        @php
                                            $after = $info['orders_after'][$orderId] ?? null;
                                            $order = $ordersDetails->get($orderId);
                                        @endphp
                                        <div class="border-b border-yellow-200 py-2">
                                            @if($order)
                                                <p class="text-sm text-yellow-700">
                                                    Заказ #{{ $order->pz_nom ?? 'N/A' }} ({{ $order->visit_data ? \Carbon\Carbon::parse($order->visit_data)->format('d.m.Y H:i') : 'N/A' }}), 
                                                    тип: 
                                                    @switch($order->type_order)
                                                        @case(1) соцтакси @break
                                                        @case(2) легковой @break
                                                        @case(3) ГАЗель @break
                                                        @default {{ $order->type_order }}
                                                    @endswitch
                                                    (ID: {{ $orderId }})
                                                </p>
                                            @else
                                                <p class="text-sm text-yellow-700">Заказ #{{ $orderId }}:</p>
                                            @endif
                                            <p class="text-xs text-yellow-600">
                                                До: цена={{ $before['taxi_price'] }}, возм={{ $before['taxi_vozm'] }}
                                                <span class="text-xs text-yellow-600 px-6">
                                                    После: цена={{ $after['taxi_price'] }}, возм={{ $after['taxi_vozm'] }}
                                                </span>
                                            </p>    
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" 
                                class="text-yellow-500 hover:text-yellow-700">
                            &times;
                        </button>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                    <p class="text-green-700">{{ session('success') }}</p>
                </div>
            @endif
            
            
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Редактировать Оператора такси</h1>

            <form action="{{ route('taxis.update', $taxi) }}" method="POST" class="bg-white shadow rounded-lg p-6 space-y-6">
                @csrf
                @method('PUT')

                <div class="bg-gray-200 space-y-4 p-2">
                    <!-- Название -->
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Оператор такси (наименование)</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $taxi->name) }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <!-- Статус life -->
                        <div>
                            <label for="life" class="block text-sm font-medium text-gray-700">Статус</label>
                            <select name="life" id="life" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="1" {{ old('life', $taxi->life) == 1 ? 'selected' : '' }}>Активен</option>
                                <option value="0" {{ old('life', $taxi->life) == 0 ? 'selected' : '' }}>Не активен</option>
                            </select>
                        </div>
                    </div>
                    <!-- Коэффициенты -->
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label for="koef" class="block text-sm font-medium text-gray-700">Стоимость 1 км пути</label>
                            <input type="number" step="any" name="koef" id="koef"
                                   value="{{ old('koef', $taxi->koef) }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="posadka" class="block text-sm font-medium text-gray-700">Стоимость посадки</label>
                            <input type="number" step="any" name="posadka" id="posadka"
                                   value="{{ old('posadka', $taxi->posadka) }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Скидки -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="koef50" class="block text-sm font-medium text-gray-700">Стоимость 1 км пути (при 50% скидке)</label>
                            <input type="number" step="any" name="koef50" id="koef50"
                                   value="{{ old('koef50', $taxi->koef50) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="posadka50" class="block text-sm font-medium text-gray-700">Посадка (при 50% скидке)</label>
                            <input type="number" step="any" name="posadka50" id="posadka50"
                                   value="{{ old('posadka50', $taxi->posadka50) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <!-- Авто и ГАЗель -->
                    <div class="grid grid-cols-2 gap-4 py-2">
                         <div>
                             <label for="zena1_auto" class="block text-sm font-medium text-gray-700">
                                 Легковой авто (при поездке в одну сторону)</label>
                             <input type="number" step="any" name="zena1_auto" id="zena1_auto"
                                    value="{{ old('zena1_auto', $taxi->zena1_auto) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                         </div>
                         <div>
                             <label for="zena2_auto" class="block text-sm font-medium text-gray-700">
                                 Легковой авто (при поездке в обе стороны)</label>
                             <input type="number" step="any" name="zena2_auto" id="zena2_auto"
                                    value="{{ old('zena2_auto', $taxi->zena2_auto) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                         </div>
                     </div>

                     <div class="grid grid-cols-2 gap-6 py-4">
                         <div>
                             <label for="zena1_gaz" class="block text-sm font-medium text-gray-700">Стоимость оплаты ГАЗель (при поездке в один конец)</label>
                             <input type="number" step="any" name="zena1_gaz" id="zena1_gaz"
                                    value="{{ old('zena1_gaz', $taxi->zena1_gaz) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                         </div>
                         <div>
                             <label for="zena2_gaz" class="block text-sm font-medium text-gray-700">Стоимость оплаты ГАЗель (при поездке в обе стороны)</label>
                             <input type="number" step="any" name="zena2_gaz" id="zena2_gaz"
                                    value="{{ old('zena2_gaz', $taxi->zena2_gaz) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                         </div>
                     </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-2">
                        <!-- Комментарий -->
                        <div class="md:col-span-8">
                            <label for="komment" class="block text-sm font-medium text-gray-700">Комментарии</label>
                            <textarea name="komment" id="komment" rows="2"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('komment', $taxi->komment) }}</textarea>
                        </div>
                        <!-- Дата для обновления цен -->
                        <div class="md:col-span-4">
                            <label for="update_date" class="block text-sm font-medium text-gray-700">Дата для обновления цен (DD.MM.YYYY)</label>
                            <input type="text" name="update_date" id="update_date" placeholder="DD.MM.YYYY"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <!-- Кнопки -->
                    <div class="flex justify-end space-x-2 pt-2">
                        <a href="{{ route('taxis.index') }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                            Отменить
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Сохранить изменения
                        </button>
                        <button type="submit" name="action" value="update_prices"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Обновить цены по новым тарифам
                        </button>
                    </div>
                </div>    
            </form>
        </div>
    </div>
</x-app-layout>