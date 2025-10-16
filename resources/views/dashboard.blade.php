<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight p-10 text-center">
    <span class="block">{{ __('ПРОГРАММА УЧЕТА ПОЕЗДОК СОЦТАКСИ') }}</span>
    <span>{{ __('Самара') }}</span>
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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

<section class="grid grid-cols-1 md:grid-cols-3 gap-6 p-10 bg-gray-100">
    
  <!-- Соцтакси -->
    <div class="p-0 rounded flex flex-col items-center">
      <a href="{{route('operator.social-taxi.index')}}">
        <img src="/img/1.png" alt="Соцтакси" class="w-full h-100 object-cover mb-4 rounded">
      </a>  
    </div>

  <!-- ГАЗель -->
  <div class="p-0 rounded flex flex-col items-center">
    <a href="{{route('operator.gazelle.index')}}">  
        <img src="/img/2.png" alt="ГАЗель" class="w-full h-100 object-cover mb-4 rounded">
    </a>    
  </div>

  <!-- Легковое авто -->
  <div class="p-0 rounded flex flex-col items-center">
      <a href="{{route('operator.car.index')}}">
          <img src="/img/3.png" alt="Легковое авто" class="w-full h-100 object-cover mb-4 rounded">
      </a>    
  </div>
</section>
</div
</x-app-layout>
