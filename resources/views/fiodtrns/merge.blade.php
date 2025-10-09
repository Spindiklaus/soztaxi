<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-2">
                <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-purple-600" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Совмещение дубликатов клиентов
                </h1>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <p class="mb-4 text-gray-700">
                    Выберите двух клиентов, которые являются дубликатами. Один будет удален, а его заказы будут перенесены на другого.
                </p>

                <form action="{{ route('fiodtrns.merge') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="source_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Клиент для удаления (источник)
                            </label>
                            <select name="source_id" id="source_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="">Выберите клиента</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">
                                        {{ $client->fio }} (ID: {{ $client->kl_id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="target_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Клиент для сохранения (приемник)
                            </label>
                            <select name="target_id" id="target_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="">Выберите клиента</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">
                                        {{ $client->fio }} (ID: {{ $client->kl_id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-white hover:bg-purple-700">
                            Совместить клиентов
                        </button>
                        <a href="{{ route('fiodtrns.index') }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 ml-2">
                            Отмена
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>