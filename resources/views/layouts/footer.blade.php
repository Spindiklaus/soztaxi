<footer class="bg-white shadow-inner mt-auto">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Ссылки -->
        <nav class="flex justify-between flex-wrap gap-y-2 text-sm text-gray-600 w-full">
            <a href="{{ route('categories.index') }}" class="hover:text-blue-500 transition">Категории</a>
            <a href="{{ route('skidka_dops.index') }}" class="hover:text-blue-500 transition">Дополнительные условия</a>
            <a href="{{ route('taxis.index') }}" class="hover:text-blue-500 transition">Операторы такси</a>
            <a href="{{ route('users.index') }}" class="hover:text-blue-500 transition">Операторы программы</a>
            <a href="{{ route('roles.index') }}" class="hover:text-blue-500 transition">Роли операторов</a>
            <a href="{{ route('fiodtrns.index') }}" class="hover:text-blue-500 transition">Клиенты</a>
            <a href="{{ route('fio_rips.index') }}" class="hover:text-blue-500 transition">RIP</a>
            <a href="{{ route('social-taxi-orders.index') }}" class="hover:text-blue-500 transition">Заказы</a>
        </nav>
        <nav class="flex justify-between flex-wrap gap-y-2 text-sm text-gray-600 w-full">
            <a href="{{ route('clear') }}" class="hover:text-blue-500 transition">Очистить кэш</a>
        </nav>

        <!-- Копирайт -->
    </div>
</footer>