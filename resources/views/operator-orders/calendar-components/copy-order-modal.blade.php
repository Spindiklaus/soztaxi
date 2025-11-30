<!-- resources/views/operator-orders/calendar-components/copy-order-modal.blade.php -->
<!-- Модальное окно для копирования заказа -->
    <div id="copy-order-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border  w-[600px] shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900">Копировать заказ СОЦТАКСИ</h3>
                <form id="copy-order-form" method="POST">
                    @csrf
                    <input type="hidden" id="copy-order-id" name="order_id">
                    <div class="mt-2 space-y-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="copy-visit-date-time" class="block text-sm font-medium text-gray-700">Дата и время поездки</label>
                                <input type="datetime-local" id="copy-visit-date-time" name="visit_data" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="copy-predv-way" class="block text-sm font-medium text-gray-700">Предварительная дальность поездки, км</label>
                                <input type="number" name="predv_way" id="copy-predv-way" 
                                   value="{{ old('predv_way') }}" 
                                   min="0" 
                                   step="0.1"
                                   placeholder="Введите предварительную дальность поездки"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                @error('predv_way')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>    
                    </div>
        
                        <div>
                            <!-- Направление через переключатели с ID для labels --- -->
                            <label class="block text-sm font-medium text-gray-700">Направление</label>
                            <div class="mt-1 inline-flex rounded-md shadow-sm" role="group">
                                <input type="radio" name="type_kuda" id="dir-tuda" value="1" checked class="hidden peer/dir-tuda" />
                                <!-- Добавлен ID -->
                                <label for="dir-tuda" id="label-dir-tuda" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-lg cursor-pointer hover:bg-gray-100 peer-checked/dir-tuda:bg-blue-600 peer-checked/dir-tuda:text-white">
                                    Туда же
                                </label>
                                <input type="radio" name="type_kuda" id="dir-obratno" value="2" class="hidden peer/dir-obratno" />
                                <!-- Добавлен ID -->
                                <label for="dir-obratno" id="label-dir-obratno" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border-t border-b border-r border-gray-300 rounded-r-lg cursor-pointer hover:bg-gray-100 peer-checked/dir-obratno:bg-blue-600 peer-checked/dir-obratno:text-white">
                                    Обратно
                                </label>
                            </div>
                        </div>
                    <div class="items-center gap-2 mt-4">
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none">Создать копию</button>
                        <button type="button" onclick="closeCopyModal()" class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400 focus:outline-none">Отмена</button>
                    </div>
                </form>
            </div>
        </div>
    </div>