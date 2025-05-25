<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Главная1') }}
                    </x-nav-link>
                </div>

                <nav class="bg-gray-800 p-4 flex justify-between items-center">
                    <ul class="flex space-x-4">
                        <li class="relative" id="home-menu">
                            <a href="#" class="text-white hover:bg-gray-700 px-3 py-2 rounded-md block relative">Главная</a>
                            <div class="hidden absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10" id="home-dropdown">
                                <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Новости</a>
                                <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Акции</a>
                                <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Каталог товаров</a>
                            </div>
                        </li>
                        <li class="relative" id="about-us-menu">
                            <a href="#" class="text-white hover:bg-gray-700 px-3 py-2 rounded-md block relative">О нас</a>
                            <div class="hidden absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10" id="about-us-dropdown">
                                <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Наша миссия</a>
                                <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Команда</a>
                                <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Награды и достижения</a>
                            </div>
                        </li>
                        <li class="relative" id="products-menu">
                            <a href="#" class="text-white hover:bg-gray-700 px-3 py-2 rounded-md block relative">Продукты</a>
                            <div class="hidden absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10" id="products-dropdown">
                                <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Электронные товары</a>
                                <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Программное обеспечение</a>
                                <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Сервисы</a>
                            </div>
                        </li>
                        <li class="relative" id="contacts-menu">
                            <a href="#" class="text-white hover:bg-gray-700 px-3 py-2 rounded-md block relative">Контакты</a>
                            <div class="hidden absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10" id="contacts-dropdown">
                                <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Адрес офиса</a>
                                <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Телефон и почта</a>
                                <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Банковские реквизиты</a>
                            </div>
                        </li>
                    </ul>
                </nav>


            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Профиль') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                             onclick="event.preventDefault();
                                            this.closest('form').submit();">
                                {{ __('Выйти') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Главная') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Профиль') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                                           onclick="event.preventDefault();
                                    this.closest('form').submit();">
                        {{ __('Выход') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>



<script>
document.addEventListener("DOMContentLoaded", function() {
    const leftMenus = document.querySelectorAll("#left-menus li.relative");
    const settingsMenu = document.querySelector("#settings-dropdown");

    // Проверка доступности правого меню
    if(settingsMenu) {
        settingsMenu.parentNode.addEventListener("click", function(e) {
            e.stopPropagation();                     // Заблокировать влияние на основную обработку
        });
    }

    // Обрабатываем левое меню
    leftMenus.forEach((menu) => {
        const trigger = menu.querySelector("a");
        const dropdown = menu.querySelector("div");

        if (trigger && dropdown) {
            trigger.addEventListener("click", function(e) {
                e.preventDefault();                  // Отмена стандартного перехода
                e.stopPropagation();                 // Заблокировать распространение события

                // Закрываем все прочие меню
                leftMenus.forEach((otherMenu) => {
                    const otherDropdown = otherMenu.querySelector("div");
                    if (otherDropdown && otherDropdown != dropdown) {
                        otherDropdown.style.display = "none";
                    }
                });

                // Переключаем текущее меню
                if (dropdown.style.display === "block") {
                    dropdown.style.display = "none";
                } else {
                    dropdown.style.display = "block";
                }
            });
        }
    });

    // Универсальная обработка окна для кликов вне меню
    window.onclick = function(event) {
        // Если клик не попал в левое меню или его спускающее меню
        if (!event.target.closest("#left-menus")) {
            // Закрываем только левое меню
            leftMenus.forEach((menu) => {
                const dropdown = menu.querySelector("div");
                if (dropdown) {
                    dropdown.style.display = "none";
                }
            });
        }
    };
});
</script>