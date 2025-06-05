<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Детали роли</h1>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="mb-4">
                    <strong>ID:</strong> {{ $role->id }}
                </div>
                <div class="mb-4">
                    <strong>Название:</strong> {{ $role->name }}
                </div>
                <div class="flex justify-end">
                    <a href="{{ route('roles.edit', $role) }}"
                       class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-md hover:bg-yellow-200">
                        Редактировать
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>