<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Импорт заказов из CSV</h1>
            
            <!-- Описание -->
            <div class="bg-white shadow rounded-lg p-4 mb-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-2">Формат CSV файла:</h2>
                <p class="text-sm text-gray-600 mb-2">
                    Формат: <code class="break-all">id;type_order;kl_id;client_tel;client_invalid;client_sopr;nmv;category_skidka;category_limit;dopus_id;skidka_dop_all;kol_p_limit;pz_nom;pz_data;adres_otkuda;adres_kuda;adres_obratno;zena_type;visit_data;predv_way;taxi_id;taxi_data;adres_trips_id;taxi_sent_at;taxi_price;taxi_way;otmena_data;otmena_taxi;closed_at;komment;user_id;created_at;updated_at;deleted_at</code>
                </p>
                <p class="text-sm text-gray-600 mb-3">
                    <strong>Важно:</strong> 
                    <ul class="list-disc pl-5 mt-1 text-sm">
                        <li>Поле <code>kl_id</code> будет использовано для поиска <code>client_id</code> в таблице клиентов</li>
                        <li>Поле <code>nmv</code> будет использовано для поиска <code>category_id</code> в таблице категорий (по NMV категории)</li>
                    </ul>
                </p>
                
                <h3 class="text-md font-medium text-gray-700 mb-1">Пример содержимого:</h3>
                <pre class="bg-gray-100 p-3 rounded text-xs overflow-x-auto">
id;type_order;kl_id;client_tel;client_invalid;client_sopr;nmv;category_skidka;category_limit;dopus_id;skidka_dop_all;kol_p_limit;pz_nom;pz_data;adres_otkuda;adres_kuda;adres_obratno;zena_type;visit_data;predv_way;taxi_id;taxi_sent_at;adres_trips_id;taxi_price;taxi_way;taxi_vozm;otmena_data;otmena_taxi;closed_at;komment;user_id;created_at;updated_at;deleted_at;visit_obratno

19;3;36 10^402702;89276974380;1538772;;600170;100;10;0;100;10;15212756-Eka;16.05.2025 09:49:04;Кирова пр. 365 п1 + инв коляска;Калинина ул. 32 (приемный покой) + инв коляска;Кирова пр. 365 п1 + инв коляска;2;19.05.2025 08:29:59;;3;16.05.2025 16:20:59;0;3625,96;;3625,96;  -   -  : :;0;02.06.2025 13:56:59;;5;16.05.2025 09:49:04;20.05.2025 16:22:04;0;19.05.2025 10:30:00
22;3;36 08^898996;89379873863;2681052;;600170;100;10;0;100;10;15212974-Das;19.05.2025 14:02:52;Кирова пр-т 365, п1+ инв. кресло;Базарная ул, 30 (МЛЦ)+ инв. кресло;Кирова пр-т 365, п1+ инв. Кресло;2;24.05.2025 12:20:00;;3;23.05.2025 09:09:00;0;3625,96378904938;;3625,96378904938;  -   -  : :;0;02.06.2025 13:56:59;;10;19.05.2025 14:02:52;19.05.2025 14:22:50;0;24.05.2025 15:00:00

                </pre>
            </div>

            <!-- Сообщения -->
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('success_count'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                    {{ session('success_count') }} записей успешно импортировано.
                </div>
            @endif

            @if(session('import_errors'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                    <p class="font-bold">Ошибки при импорте:</p>
                    <ul class="list-disc pl-5 mt-2">
                        @foreach(session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Форма загрузки -->
            <form action="{{ route('import.orders.process') }}" method="POST" enctype="multipart/form-data" class="bg-white shadow rounded-lg p-6">
                @csrf
                <div class="mb-6">
                    <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-2">Выберите CSV-файл</label>
                    <input type="file" name="csv_file" id="csv_file" required accept=".csv,.txt"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="mt-1 text-sm text-gray-500">Поддерживаются файлы с расширением .csv и .txt</p>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('social-taxi-orders.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                        Отмена
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Загрузить
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>