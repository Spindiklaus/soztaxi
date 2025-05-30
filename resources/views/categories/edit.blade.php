<x-app-layout>

<div class="container">
    <form method="POST" action="{{ route('categories.update', $category) }}">
        @csrf @method('PUT')
        @include('categories.form')
        <button type="submit" class="btn btn-primary">Обновить</button>
    </form>
</div>

</x-app-layout>