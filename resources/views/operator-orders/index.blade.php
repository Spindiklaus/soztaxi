<!-- resources/views/operator-orders/index.blade.php -->
<x-app-layout>
    <div class="bg-gray-100 py-0">
        <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
                @php
                    $typeName = '';
                    $typeColor = '';
                    switch(request('filter_type_order')) {
                        case 1: 
                            $typeName = 'Соцтакси';
                            $typeColor = 'text-blue-600';
                            $theadColor = 'bg-blue-800';
                            $hoverColor = 'bg-blue-700';
                            $tableInclude = 'operator-orders.index-components.table_soz';
                            break;
                        case 2: 
                            $typeName = 'Легковое авто';
                            $typeColor = 'text-green-600';
                            $theadColor = 'bg-green-600';
                            $hoverColor = 'bg-green-500';
                            $tableInclude = 'operator-orders.index-components.table_la';
                            break;
                        case 3: 
                            $typeName = 'ГАЗель';
                            $typeColor = 'text-yellow-600';
                            $theadColor = 'bg-yellow-600';
                            $hoverColor = 'bg-yellow-500';
                            $tableInclude = 'operator-orders.index-components.table_gaz';
                            break;
                    }
                @endphp
<!--                <h1 class="text-2xl font-bold text-gray-800">Мои заказы: <span class="{{-- $typeColor --}}">{{-- $typeName --}}</span></h1>-->
                
                
            
            <!-- Фильтры -->
            @include('operator-orders.index-components.filters')
            <!-- Пагинация -->
<!--            <div class="mt-1 mb-1">
                {{-- $orders->links() --}}
            </div>-->
            <!-- Таблица заказов -->
            @include($tableInclude)

             <!--Пагинация--> 
            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</x-app-layout>