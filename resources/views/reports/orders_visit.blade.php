<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Сводка по статусам заказов</h2>

            <form method="GET" action="{{ route('orders.report_visit') }}" class="mb-6 flex space-x-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Дата начала</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Дата окончания</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
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
                            @forelse($report as $row)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $row->visit_date ? \Carbon\Carbon::parse($row->visit_date)->format('d.m.Y') : 'Не указана' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $row->status_1_count ?: '' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $row->status_2_count ?: '' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $row->status_3_count ?: '' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $row->status_4_count ?: '' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    Нет данных
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>