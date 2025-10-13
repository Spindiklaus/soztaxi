{{-- resources/views/orders/grouping_view.blade.php --}}
 <x-app-layout>
        <div class="container mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Группировка заказов в поездки на {{ $selectedDate->format('d.m.Y') }}</h2>
                <!-- Отображение использованных параметров -->
            <div class="mb-4 p-3 bg-blue-50 rounded-md">
                <p class="text-sm text-blue-800"><strong>Параметры группировки:</strong></p>
                <p class="text-sm text-blue-800">- Допустимая разница во времени заказов: {{ $timeTolerance }} минут</p>
                <p class="text-sm text-blue-800">- Минимальное сходство адреса КУДА: {{ $addressTolerance }}%</p>
                <p class="text-sm text-blue-800">- Максимальный размер потенциальной группы: {{ $maxPotentialGroupSize }} чел.</p>
                <p class="text-sm text-blue-800">- Максимальное число пассажиров в авто: 3 чел.</p>
            </div>

                <form id="grouping-form" action="{{ route('orders.grouping.process') }}" method="POST">
                    @csrf
                    <input type="hidden" name="selected_date" value="{{ $selectedDate->format('Y-m-d') }}">

                    @if(empty($potentialGroups))
                        <p class="text-gray-700 mb-4">На выбранную дату нет подходящих заказов для автоматической группировки.</p>
                    @else
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Предлагаемые группы (отметьте нужные):</h3>
                        @foreach($potentialGroups as $index => $group)
                            <div class="card mb-3 potential-group-card bg-gray-50 border border-gray-200 rounded-lg p-4" data-group-id="{{ $group['id'] }}">
                                <div class="card-header bg-gray-100 border-b border-gray-200 rounded-t-lg p-3">
                                    <input type="checkbox" name="selected_groups[{{ $index }}][selected]" value="1" class="group-selector h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <strong class="ml-2">{{ $group['name'] }}</strong>
                                    <span class="badge bg-blue-100 text-blue-800 text-xs px-2 py-1 ml-2 rounded">Время: {{ $group['base_time']->format('H:i') }} +/- {{ $timeTolerance }} мин</span>
                                </div>
                                <div class="card-body p-3">
                                    <ul class="list-group">
                                        @foreach($group['orders'] as $order)
                                            <li class="list-group-item p-2 border-b border-gray-100 bg-white">
                                                <input type="checkbox" name="selected_groups[{{ $index }}][order_ids][]" value="{{ $order->id }}" class="order-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" disabled>
                                                <div class="ml-2">
                                                    <strong>{{ $order->pz_nom }}</strong> - 
                                                    <span 
                                                        @if($order->has_duplicate_time)
                                                            class="bg-red-500 text-white px-1 rounded font-bold"
                                                        @endif
                                                    >
                                                        {{ $order->visit_data->format('H:i') }}
                                                    </span>
                                                    <br>
                                                    <span class="text-sm text-gray-600">Клиент: {{ $order->client ? $order->client->fio : 'N/A' }}</span>
                                                    <br>
                                                    <span class="text-sm text-gray-600">От: <b>{{ $order->adres_otkuda }}</b></span>
                                                    <span class="text-sm text-gray-600">До: <b>{{ $order->adres_kuda }}</b></span>
                                                     @if($order->predv_way !== null && $order->predv_way !== '')
                                                        <br>
                                                        Предв. дальность, км: <span class="text-sm text-gray-600"><b>{{ number_format($order->predv_way, 3, ',', ' ') }}</b></span>
                                                    @endif
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endforeach
                        <button type="submit" class="btn btn-success mt-3 inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition ease-in-out duration-150" id="save-groups-btn" disabled>
                            Сохранить выбранные группы
                        </button>
                    @endif


                </form>

                <a href="{{ route('orders.grouping.form') }}" class="btn btn-secondary mt-3 inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150 ml-2">Назад</a>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const groupSelectors = document.querySelectorAll('.group-selector');
                const saveButton = document.getElementById('save-groups-btn');

                groupSelectors.forEach(selector => {
                    selector.addEventListener('change', function() {
                        const card = this.closest('.potential-group-card');
                        const orderCheckboxes = card.querySelectorAll('.order-checkbox');

                        orderCheckboxes.forEach(cb => {
                            cb.disabled = !this.checked;
                            if (!this.checked) {
                                cb.checked = false;
                            }
                        });

                        updateSaveButton();
                    });
                });

                function updateSaveButton() {
                    let anyGroupSelected = false;
                    groupSelectors.forEach(selector => {
                        if (selector.checked) {
                            anyGroupSelected = true;
                        }
                    });
                    saveButton.disabled = !anyGroupSelected;
                }

                updateSaveButton();
            });
        </script>
    </x-app-layout>