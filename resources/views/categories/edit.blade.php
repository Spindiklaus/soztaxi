<x-app-layout>

    <form method="POST" action="{{ route('categories.update', $category) }}">
        @csrf @method('PUT')
        @include('categories.form')
        <button type="submit" class="btn btn-primary">Обновить</button>
    </form>

</x-app-layout>