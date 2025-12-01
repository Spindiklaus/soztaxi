<!-- resources/views/operator-orders/calendar-components/client-info.blade.php -->

<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-2">
    <div class="bg-gray-50 px-4  rounded-t-lg">
        <button type="button"
                onclick="toggleClientInfo()"
                class="flex items-center justify-between w-full text-left">
            <h2 class="text-lg font-semibold text-gray-800">
                {{ $client->fio }}
            </h2>
            <svg id="client-info-arrow" class="h-5 w-5 transform transition-transform text-gray-500"
                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>

    <div id="client-info-content" class="p-4 hidden">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-2">
            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-gray-700">Категория инвалидности</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md"
                     title="Cкидка %: {{$latestOrder->skidka_dop_all}}, Лимит: {{$latestOrder->kol_p_limit}}, Доп.условия: {{ $latestOrder->dopus?->name ?? '-' }} "
                >
                    @if($lastCategory)
                        {{ $lastCategory->name }} (NMV: {{ $lastCategory->nmv }})
                    @else
                        Не указана
                    @endif
                </div>
            </div>
            <!-- Адрес "откуда" -->
            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-gray-700">Адрес "откуда"</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md"
                     title="{{ $latestOrder->adres_otkuda}} {{ $latestOrder->adres_otkuda_info}}"
                >
                    {{  Str::limit( $latestOrder->adres_otkuda, 40) }}
                </div>
            </div>
            <!-- Адрес "куда" -->
            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-gray-700">Адрес "куда"</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md"
                    title="{{ $latestOrder->adres_kuda}} {{ $latestOrder->adres_kuda_info}}"
                >
                    {{ Str::limit( $latestOrder->adres_kuda, 40) }}
                </div>
            </div>
        </div>
    </div>
</div>