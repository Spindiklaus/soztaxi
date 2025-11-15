<!-- resources/views/operator-orders/calendar-components/client-info.blade.php -->

<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
    <div class="bg-gray-50 px-4 py-3 rounded-t-lg">
        <button type="button"
                onclick="toggleClientInfo()"
                class="flex items-center justify-between w-full text-left">
            <h2 class="text-lg font-semibold text-gray-800">
                Информация о клиенте {{ $client->fio }}:
            </h2>
            <svg id="client-info-arrow" class="h-5 w-5 transform transition-transform text-gray-500"
                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>

    <div id="client-info-content" class="p-4 hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Серия и номер паспорта</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ $client->kl_id ?? 'Не указан' }}</div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Дата рождения</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ $client->data_r ? $client->data_r->format('d.m.Y') : 'Не указана' }}</div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Пол</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ $client->sex ?? 'Не указан' }}</div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Дата смерти (RIP)</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md">
                    @if($client->rip_at)
                        {{ $client->rip_at->format('d.m.Y') }}
                        (установлено {{ $client->created_rip ? $client->created_rip->format('d.m.Y H:i') : 'неизвестно' }})
                    @else
                        Не установлено
                    @endif
                </div>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Комментарий</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ $client->komment ?? 'Нет' }}</div>
            </div>
        </div>
    </div>
</div>