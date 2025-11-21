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

                    <!-- Оборачиваем dropdown "Ввод данных" в div с теми же классами, что и у x-nav-link -->
                    <div class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                        <x-dropdown align="bottom" width="48">
                            <x-slot name="trigger">
                                <!-- Кнопка внутри триггера получает минимальные стили для выравнивания содержимого -->
                                <button class="flex items-center text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                    <div>{{ __('Заказы') }}</div> <!-- Название подменю -->
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('operator.social-taxi.index')" :active="request()->routeIs('operator.social-taxi.*')">
                                    {{ __('Соцтакси') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('operator.gazelle.index')" :active="request()->routeIs('operator.gazelle.*')">
                                    {{ __('ГАЗель') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('operator.car.index')" :active="request()->routeIs('operator.car.*')">
                                    {{ __('Легковой автомобиль') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    <!-- Конец подменю "Ввод данных" -->
                    
                     <!-- подменю "Управление поездками" -->
                    <div class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                        <x-dropdown align="bottom" width="48">
                            <x-slot name="trigger">
                                <!-- Кнопка внутри триггера получает минимальные стили для выравнивания содержимого -->
                                <button class="flex items-center text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                    <div>{{ __('Группы') }}</div> <!-- Название подменю -->
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>
                             <x-slot name="content">
                                <x-dropdown-link :href="route('orders.grouping.form')" :active="request()->routeIs('orders.grouping.form')">
                                    {{ __('Группировка заказов в поездки (КУДА)') }}
                                </x-dropdown-link> 
                                <x-dropdown-link :href="route('order-groups.index')" :active="request()->routeIs('order-groups.index')">
                                    {{ __('Поездки') }}
                                </x-dropdown-link>
                             </x-slot>
                        </x-dropdown>
                    </div>
                    <!-- Конец подменю "Управление поездками" -->
                    

                    <!-- подменю "Управление заказами" -->
                    <div class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                        <x-dropdown align="bottom" width="48">
                            <x-slot name="trigger">
                                <!-- Кнопка внутри триггера получает минимальные стили для выравнивания содержимого -->
                                <button class="flex items-center text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                    <div>{{ __('Управление заказами') }}</div> <!-- Название подменю -->
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('taxi-orders.index')" :active="request()->routeIs('taxi-orders.index')">
                                    {{ __('Передача в такси') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('taxi_sent-orders.index')" :active="request()->routeIs('taxi_sent-orders.index')">
                                    {{ __('Отмена передачи') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('social-taxi-orders.close.index')" :active="request()->routeIs('social-taxi-orders.close.index')">
                                    {{ __('Закрыть заказы') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('social-taxi-orders.open.index')" :active="request()->routeIs('social-taxi-orders.open.index')">
                                    {{ __('Открыть заказы') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    <!-- Конец подменю "Управление заказами" -->


                    <x-nav-link :href="route('fiodtrns.index')" :active="request()->routeIs('fiodtrns.index')">
                        {{ __('Клиенты') }}
                    </x-nav-link>                   

                    <!-- подменю "Отчеты" -->
                    <div class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                        <x-dropdown align="bottom" width="48">
                            <x-slot name="trigger">
                                <!-- Кнопка внутри триггера получает минимальные стили для выравнивания содержимого -->
                                <button class="flex items-center text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                    <div>{{ __('Отчеты') }}</div> <!-- Название подменю -->
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>
                             <x-slot name="content">
                                <x-dropdown-link :href="route('orders.report_visit')" :active="request()->routeIs('orders.report_visit')">
                                    {{ __('Сводный отчет') }}
                                </x-dropdown-link> 
                             </x-slot>
                        </x-dropdown>
                    </div>
                    <!-- Конец подменю "Отчеты" -->                    
                </div>
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
                {{ __('Программа учета поездок') }}
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