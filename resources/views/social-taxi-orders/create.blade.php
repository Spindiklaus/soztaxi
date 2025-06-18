<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Создать заказ</h1>

            <form action="{{ route('social-taxi-orders.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="type_order" class="block text-sm font-medium text-gray-700">Тип заказа</label>
                    <select name="type_order" id="type_order" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="1">Соцтакси</option>
                        <option value="2">Легковое авто</option>
                        <option value="3">ГАЗель</option>
                    </select>
                </div>

                <div>
                    <label for="client_id" class="block text-sm font-medium text-gray-700">Клиент</label>
                    <select name="client_id" id="client_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="client_tel" class="block text-sm font-medium text-gray-700">Телефон клиента</label>
                    <input type="text" name="client_tel" id="client_tel" value="{{ old('client_tel') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Создать
                </button>
            </form>
        </div>
    </div>
</x-app-layout>