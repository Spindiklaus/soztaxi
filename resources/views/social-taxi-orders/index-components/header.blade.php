<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Заказы</h1>

    <!-- social-taxi-orders.index-components/header.blade.php -->
    <div class="space-x-2 flex relative">
        <a href="{{ route('import.orders.form') }}"
           class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700"
           title="Импортировать заказы из CSV">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Импортировать CSV
        </a>

        <!-- Кнопка с выпадающим меню -->
        <div class="relative inline-block text-left">
            <button type="button" 
                    id="create-order-menu-button"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150"
                    title="Создать запись">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Добавить заказ
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Выпадающее меню -->
            <div id="create-order-dropdown" 
                 class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden z-50">
                <div class="py-1" role="none">
                    <a href="{{ route('social-taxi-orders.create.by-type', 1) }}" 
                       class="block px-4 py-2 text-sm text-blue-700 hover:bg-blue-100" role="menuitem">
                        <span class="font-medium">Соцтакси</span>
                    </a>
                    <a href="{{ route('social-taxi-orders.create.by-type', 2) }}" 
                       class="block px-4 py-2 text-sm text-green-700 hover:bg-green-100" role="menuitem">
                        <span class="font-medium">Легковое авто</span>
                    </a>
                    <a href="{{ route('social-taxi-orders.create.by-type', 3) }}" 
                       class="block px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-100" role="menuitem">
                        <span class="font-medium">ГАЗель</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript для выпадающего меню -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuButton = document.getElementById('create-order-menu-button');
    const dropdown = document.getElementById('create-order-dropdown');
    
    if (menuButton && dropdown) {
        menuButton.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('hidden');
        });
        
        // Закрываем меню при клике вне его области
        document.addEventListener('click', function(event) {
            if (!menuButton.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }
});
</script>