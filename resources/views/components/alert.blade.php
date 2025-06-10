@props(['type' => 'info', 'title' => null])

<div x-data="{ show: true }"
     x-show="show"
     class="relative bg-white border-l-4 p-4 mb-4 rounded-md shadow-sm max-w-xl mx-auto"
     :class="{
        'border-red-500 bg-red-50 text-red-700': '{{ $type }}' === 'error',
        'border-green-500 bg-green-50 text-green-700': '{{ $type }}' === 'success',
        'border-yellow-500 bg-yellow-50 text-yellow-700': '{{ $type }}' === 'warning',
        'border-blue-500 bg-blue-50 text-blue-700': '{{ $type }}' === 'info'
    }">

    <!-- Кнопка закрытия -->
    <svg @click="show = false" class="absolute top-1 right-0 w-6 h-6 text-red-500 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
</svg>

    <!-- Заголовок -->
     @if($title)
        <p class="font-semibold">{{ $title }}</p>
    @endif

    <!-- Содержимое -->
    <div class="mt-2 text-sm">
        {{ $slot }}
    </div>
</div>