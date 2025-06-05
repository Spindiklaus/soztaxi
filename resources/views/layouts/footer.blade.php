<footer class="bg-white shadow-inner mt-auto">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Ссылки -->
        <nav class="flex justify-between flex-wrap gap-y-2 text-sm text-gray-600 w-full">
            <a href="{{ route('categories.index') }}" class="hover:text-blue-500 transition">Категории</a>
            <a href="{{ route('users.index') }}" class="hover:text-blue-500 transition">Операторы</a>
            <a href="{{ route('roles.index') }}" class="hover:text-blue-500 transition">Роли</a>
            <a href="{{ route('clear') }}" class="hover:text-blue-500 transition">Очистить кэш</a>
        </nav>

        <!-- Копирайт -->
    </div>
</footer>