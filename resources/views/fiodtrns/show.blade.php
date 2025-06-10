<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Сведения по клиенту</h1>

            <div class="bg-white shadow rounded-lg p-6">
                <div class="mb-4">
                    <strong>ID клиента:</strong> {{ $fiodtrn->kl_id }}
                </div>
                <div class="mb-4">
                    <strong>ФИО:</strong> {{ $fiodtrn->fio }}
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div><strong>Дата рождения:</strong> {{ optional($fiodtrn->data_r)->format('d.m.Y') }}</div>
                    <div><strong>Пол:</strong> {{ $fiodtrn->sex === 'М' ? 'Мужской' : ($fiodtrn->sex === 'Ж' ? 'Женский' : '-') }}</div>
                </div>
                <div class="mb-4">
                    <strong>RIP дата:</strong> {{ optional($fiodtrn->rip_at)->format('d.m.Y H:i') ?: '-' }}
                </div>
                <div class="mb-4">
                    <strong>Комментарии:</strong> {{ $fiodtrn->komment ?: '-' }}
                </div>
                <div class="mb-4">
                    <strong>Оператор:</strong> {{ optional($fiodtrn->user)->name ?? '-' }}
                </div>

                <!-- Кнопка редактирования -->
                <div class="flex justify-end">
                     <a href="{{ route('fiodtrns.index', ['sort' => request('sort', 'id'), 'direction' => request('direction', 'asc') ]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                        К списку
                    </a>
                    <a href="{{ route('fiodtrns.edit', ['fiodtrn' => $fiodtrn, 'sort' => request('sort'), 'direction' => request('direction')]) }}"
                       class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-md hover:bg-yellow-200">
                        Редактировать
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>