<div id="closure" class="tab-content hidden">
    <div class="bg-gray-50 p-4 rounded-lg mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Закрытие (отмена) заказа</h2>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Дата отмены</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md">
                    {{ $order->cancelled_at ? $order->cancelled_at->format('d.m.Y H:i') : 'Не указана' }}
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Дата закрытия</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md">
                    {{ $order->closed_at ? $order->closed_at->format('d.m.Y H:i') : 'Не указана' }}
                </div>
            </div>
            
            
        </div>
    </div>
    
    <div class="bg-gray-50 p-4 rounded-lg">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Комментарии</h2>
        
        <div class="bg-white p-4 rounded-md border">
            <p class="text-sm text-gray-600">{{ $order->komment ?? 'Комментарии отсутствуют' }}</p>
        </div>
    </div>
</div>