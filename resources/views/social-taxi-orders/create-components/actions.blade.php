<!-- Кнопки действия -->
<!-- resources/views/social-taxi-orders/create-components/actions.blade.php -->
<div class="flex justify-end space-x-3">
    <a href="{{ route('social-taxi-orders.index') }}" 
       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
        Отмена
    </a>
    <button type="submit" 
            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        Создать заказ
    </button>
</div>