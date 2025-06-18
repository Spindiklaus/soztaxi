<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Отображение ошибок -->
            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                    <p class="font-bold">Ошибки валидации:</p>
                    <ul class="list-disc pl-5 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Редактировать скидку</h1>

            <form action="{{ route('skidka_dops.update', $skidkaDop) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Наименование</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $skidkaDop->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('name')
                        <p class="text-red-500 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="skidka" class="block text-sm font-medium text-gray-700">Скидка (%)</label>
                    <input type="number" name="skidka" id="skidka" value="{{ old('skidka', $skidkaDop->skidka) }}" 
                           min="0" max="100" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('skidka')
                        <p class="text-red-500 text-sm">{{ $message }}</p>
                    @enderror                    
                </div>

                <div>
                    <label for="kol_p" class="block text-sm font-medium text-gray-700">Лимит поездок</label>
                    <input type="number" name="kol_p" id="kol_p" value="{{ old('kol_p', $skidkaDop->kol_p) }}" 
                           min="0" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('kol_p')
                        <p class="text-red-500 text-sm">{{ $message }}</p>
                    @enderror                    
                </div>

                <div>
                    <label for="life" class="block text-sm font-medium text-gray-700">Действующий</label>
                    <select name="life" id="life" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="1" {{ old('life', $skidkaDop->life) == '1' ? 'selected' : '' }}>Да</option>
                        <option value="0" {{ old('life', $skidkaDop->life) == '0' ? 'selected' : '' }}>Нет</option>
                    </select>
                    @error('life')
                        <p class="text-red-500 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="komment" class="block text-sm font-medium text-gray-700">Комментарии</label>
                    <textarea name="komment" id="komment" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('komment', $skidkaDop->komment) }}</textarea>
                    @error('komment')
                        <p class="text-red-500 text-sm">{{ $message }}</p>
                    @enderror    
                </div>

                <div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                        Обновить
                    </button>
                    <a href="{{ route('skidka_dops.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                        Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>