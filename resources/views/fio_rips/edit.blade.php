<form action="{{ route('fio_rips.update', $fioRip) }}" method="POST" class="space-y-4">
    @csrf
    @method('PUT')

    <div>
        <label for="kl_id" class="block text-sm font-medium text-gray-700">ID клиента (серия^номер)</label>
        <input type="text" name="kl_id" id="kl_id" value="{{ old('kl_id', $fioRip->kl_id) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
    </div>

    <!-- Остальные поля аналогичны create.blade.php -->

    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Обновить</button>
</form>