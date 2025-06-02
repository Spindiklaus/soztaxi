<x-app-layout>

    <form method="POST" action="{{ route('categories.store') }}">
        @csrf
        @include('categories.form')
<!--        <button type="submit" class="btn btn-success">Сохранить</button>-->
    </form>

</x-app-layout>