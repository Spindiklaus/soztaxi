<!-- Льготы по поездке -->
<!-- resources/views/social-taxi-orders/create-components/benefits.blade.php -->
<div class="bg-gray-50 p-2 rounded-lg mb-2">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Дополнительные льготы по поездке</h2>

    <div class="space-y-2">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-2">
            <div class="md:col-span-6">
                <label for="dopus_id" class="block text-sm font-medium text-gray-700">Дополнительные условия <br/>для скидок</label>
                <select name="dopus_id" id="dopus_id"
                         @if($type != 1) readonly disabled @endif
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Выберите допусловия</option>
                    @foreach($dopusConditions as $dopus)
                        <option value="{{ $dopus->id }}" {{ old('dopus_id', $copiedOrder->dopus_id ?? '') == $dopus->id ? 'selected' : '' }}>
                            {{ $dopus->name }} (Скидка: {{ $dopus->skidka }}%, Лимит: {{ $dopus->kol_p }} поездок/мес)
                        </option>
                    @endforeach
                </select>
                 @if($type != 1)
                    <p class="mt-1 text-xs text-gray-500">доступны только для соцтакси</p>
                 @endif
                @error('dopus_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

        
            <div class="md:col-span-3">
                <label for="skidka_dop_all" class="block text-sm font-medium text-gray-700">Окончательная скидка инвалиду, %</label>
                <input type="number" name="skidka_dop_all" id="skidka_dop_all" 
                       value="{{ old('skidka_dop_all', $copiedOrder->skidka_dop_all ?? '') }}"
                       min="50" max="100" step="1"
                       placeholder=""
                       readonly
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 cursor-not-allowed">
                    @error('skidka_dop_all')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
            </div>

            <div class="md:col-span-3">
                <label for="kol_p_limit" class="block text-sm font-medium text-gray-700">Окончательный лимит поездок</label>
                <input type="number" name="kol_p_limit" id="kol_p_limit" 
                       value="{{ old('kol_p_limit', $copiedOrder->kol_p_limit ?? '') }}"
                       min="10" max="26" step="1"
                       placeholder=""
                       readonly
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 cursor-not-allowed">
                    @error('kol_p_limit')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
            </div>
        </div>    
    </div>
</div>