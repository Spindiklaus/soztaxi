<x-app-layout>
    <div class="bg-gray-100 py-2">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-2">
                <h2 class="text-2xl font-bold text-gray-800">Сводный отчет по статусам заказов</h2>
                <a href="{{ route('orders.report_visit_export', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none">
                    Экспорт в Excel
                </a>
            </div>

            <form method="GET" action="{{ route('orders.report_visit') }}" class="mb-2 flex space-x-4 items-end">
                <div class="px-2">
                    <label class="block text-sm font-medium text-gray-700">Дата начала</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>
                <div class="px-2">
                    <label class="block text-sm font-medium text-gray-700">Дата окончания</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="mt-1   block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none">
                    Фильтровать
                </button>
            </form>

            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-blue-800 text-gray-200">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                    Дата поездки
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                    Тип заказа
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                    Принят (id=1)
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                    Передан в такси (id=2)
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                    Отменен (id=3)
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                    Закрыт (id=4)
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($report as $date => $data)
                                @php $first = true; @endphp
                                @foreach($data['types'] as $typeId => $stats)
                                <tr>
                                    @if($first)
                                        <td class="px-6 py-1 whitespace-nowrap text-sm text-gray-900" rowspan="{{ count($data['types']) }}">
                                            {{ $date ? \Carbon\Carbon::parse($date)->format('d.m.Y') : 'Не указана' }}
                                        </td>
                                        @php $first = false; @endphp
                                    @endif
                                    <td class="px-6 py-1 whitespace-nowrap text-sm {{ getOrderTypeColor($typeId) }}">
                                        {{ getOrderTypeName($typeId) }}
                                    </td>
                                    <!-- Статус 1 (Принят) -->
                                    <td class="px-6 py-1 whitespace-nowrap text-sm">
                                        @if($stats['status_1_count'])
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-200 text-blue-800 cursor-pointer hover:bg-blue-200" onclick="showOrdersByStatus('{{ $date }}', {{ $typeId }}, 1)">
                                            <i data-feather="check-circle" class="w-4 h-4 mr-1"></i>
                                            {{ $stats['status_1_count'] }}
                                        </span>
                                        @endif
                                    </td>
                                    <!-- Статус 2 (Передан в такси) -->
                                    <td class="px-6 py-1 whitespace-nowrap text-sm">
                                        @if($stats['status_2_count'])
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 cursor-pointer hover:bg-yellow-200" onclick="showOrdersByStatus('{{ $date }}', {{ $typeId }}, 2)">
                                            <i data-feather="truck" class="w-4 h-4 mr-1"></i>
                                            {{ $stats['status_2_count'] }}
                                        </span>
                                        @endif
                                    </td>
                                    <!-- Статус 3 (Отменен) -->
                                    <td class="px-6 py-1 whitespace-nowrap text-sm">
                                        @if($stats['status_3_count'])
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-200 text-red-800 cursor-pointer hover:bg-red-200" onclick="showOrdersByStatus('{{ $date }}', {{ $typeId }}, 3)">
                                            <i data-feather="x-circle" class="w-4 h-4 mr-1"></i>
                                            {{ $stats['status_3_count'] }}
                                        </span>
                                        @endif
                                    </td>
                                    <!-- Статус 4 (Закрыт) -->
                                    <td class="px-6 py-1 whitespace-nowrap text-sm">
                                        @if($stats['status_4_count'])
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 cursor-pointer hover:bg-green-200" onclick="showOrdersByStatus('{{ $date }}', {{ $typeId }}, 4)">
                                            <i data-feather="flag" class="w-4 h-4 mr-1"></i>
                                            {{ $stats['status_4_count'] }}
                                        </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        Нет данных
                                    </td>
                                </tr>
                            @endforelse

                            <!-- Итоговая строка -->
                            <tr class="bg-gray-100 font-semibold">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Итого</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($totals['totalStatus1'])
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-200 text-blue-800">
                                        <i data-feather="check-circle" class="w-4 h-4 mr-1"></i>
                                        {{ $totals['totalStatus1'] }}
                                    </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($totals['totalStatus2'])
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i data-feather="truck" class="w-4 h-4 mr-1"></i>
                                        {{ $totals['totalStatus2'] }}
                                    </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($totals['totalStatus3'])
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-200 text-red-800">
                                        <i data-feather="x-circle" class="w-4 h-4 mr-1"></i>
                                        {{ $totals['totalStatus3'] }}
                                    </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($totals['totalStatus4'])
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i data-feather="flag" class="w-4 h-4 mr-1"></i>
                                        {{ $totals['totalStatus4'] }}
                                    </span>
                                    @endif
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>



        </div>
    </div>
    @include('reports.client-trips-modal')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            feather.replace();
        });
    </script>
     @include('reports.scripts.main')
</x-app-layout>