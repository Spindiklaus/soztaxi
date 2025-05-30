@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Категория: {{ $category->name }}</h1>
    <table class="table">
        <tr><th>ID</th><td>{{ $category->id }}</td></tr>
        <tr><th>NMV</th><td>{{ $category->nmv }}</td></tr>
        <tr><th>Название</th><td>{{ $category->name }}</td></tr>
        <tr><th>Скидка</th><td>{{ $category->skidka }}%</td></tr>
        <tr><th>Поездок в месяц</th><td>{{ $category->kol_p }}</td></tr>
        <tr><th>Оператор</th><td>{{ $category->user?->name ?? '-' }}</td></tr>
        <tr><th>Соцтакси</th><td>{{ $category->is_soz ? 'Да' : 'Нет' }}</td></tr>
        <tr><th>Легковой авто</th><td>{{ $category->is_auto ? 'Да' : 'Нет' }}</td></tr>
        <tr><th>ГАЗель</th><td>{{ $category->is_gaz ? 'Да' : 'Нет' }}</td></tr>
        <tr><th>Комментарий</th><td>{{ $category->komment }}</td></tr>
    </table>

    <a href="{{ route('categories.edit', $category) }}" class="btn btn-warning">Редактировать</a>
    <form action="{{ route('categories.destroy', $category) }}" method="POST" style="display:inline-block;">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger">Удалить</button>
    </form>
</div>
@endsection