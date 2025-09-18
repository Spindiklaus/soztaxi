<!-- Сведения о клиенте -->
<!-- resources/views/social-taxi-orders/edit-components/client-info.blade.php -->
<div class="bg-gray-50 p-4 rounded-lg mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Сведения о клиенте</h2>

    <div class="space-y-4">
        <div>
            <label for="client_id" class="block text-sm font-medium text-gray-700">Клиент *</label>
            <select name="client_id" id="client_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Выберите клиента</option>
                @foreach($clients as $client)
                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                    {{ $client->fio }} (#{{ $client->id }})
                </option>
                @endforeach
            </select>
            @error('client_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="category_id" class="block text-sm font-medium text-gray-700">Категория инвалидности *</label>
            <select name="category_id" id="category_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Выберите категорию</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                    {{ $category->nmv }} - {{ $category->name }} (Скидка: {{ $category->skidka }}%, Лимит: {{ $category->kol_p }} поездок/мес)
                </option>
                @endforeach
            </select>
            @error('category_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Добавлены поля category_skidka и category_limit -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="category_skidka" class="block text-sm font-medium text-gray-700">Скидка по категории, %</label>
                <input type="number" name="category_skidka" id="category_skidka" 
                       value="{{ old('category_skidka') }}"
                       min="0" max="100" step="1"
                       placeholder="Введите скидку по категории"
                       readonly
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 cursor-not-allowed">
                    @error('category_skidka')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
            </div>

            <div>
                <label for="category_limit" class="block text-sm font-medium text-gray-700">Лимит поездок по категории</label>
                <input type="number" name="category_limit" id="category_limit" 
                       value="{{ old('category_limit') }}"
                       min="10" max="26" step="1"
                       placeholder="Введите лимит поездок по категории"
                       readonly
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 cursor-not-allowed">
                    @error('category_limit')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
            </div>
        </div>

        <div>
            <label for="client_tel" class="block text-sm font-medium text-gray-700">Телефон для связи*</label>
            <input type="text" name="client_tel" id="client_tel" 
                   value="{{ old('client_tel') }}"
                   placeholder="Введите телефон для связи"
                   required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('client_tel')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
        </div>

        <div>
            <label for="client_invalid" class="block text-sm font-medium text-gray-700">Удостоверение инвалида</label>
            <input type="text" name="client_invalid" id="client_invalid" 
                   value="{{ old('client_invalid') }}"
                   placeholder="Введите номер удостоверения"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('client_invalid')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
        </div>

        <div>
            <label for="client_sopr" class="block text-sm font-medium text-gray-700">ФИО (сопровождающий)</label>
            <input type="text" name="client_sopr" id="client_sopr" 
                   value="{{ old('client_sopr') }}"
                   placeholder="Введите ФИО сопровождающего"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('client_sopr')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
        </div>
    </div>
</div>