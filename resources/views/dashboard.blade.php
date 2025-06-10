<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight p-10 text-center">
            {{ __('Программа учета поездок соцтакси. Самара') }} 
        </h2>
    </x-slot>

<!--    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("Вы залогированы!") }}
                </div>
            </div>
        </div>
    </div>-->

<section class="grid grid-cols-1 md:grid-cols-3 gap-6 p-10 bg-gray-100">
  <!-- Соцтакси -->
  <div class="p-6 bg-white rounded shadow flex flex-col items-center">
    <img src="/img/auto_home.jpg" alt="Соцтакси" class="w-full h-100 object-cover mb-4 rounded">
    <span class="text-center font-medium">Соцтакси</span>
  </div>

  <!-- ГАЗель -->
  <div class="p-6 bg-white rounded shadow flex flex-col items-center">
    <img src="/img/gaz_home1.jpg" alt="ГАЗель" class="w-full h-100 object-cover mb-4 rounded">
    <span class="text-center font-medium">ГАЗель</span>
  </div>

  <!-- Легковое авто -->
  <div class="p-6 bg-white rounded shadow flex flex-col items-center">
    <img src="/img/sedan_home.jpg" alt="Легковое авто" class="w-full h-100 object-cover mb-4 rounded">
    <span class="text-center font-medium">Легковое авто</span>
  </div>
</section>

</x-app-layout>
