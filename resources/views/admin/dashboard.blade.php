@extends('layouts.app')

@section('content')
<div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <div class="w-64 bg-white border-r">
        <div class="p-4 text-xl font-bold">Админка</div>
        <nav class="p-4 space-y-2">
            <a href="/admin/dashboard" class="block px-4 py-2 text-blue-600 hover:bg-blue-100 rounded">Главная</a>
            <a href="/admin/users" class="block px-4 py-2 hover:bg-blue-100 rounded">Пользователи</a>
            <a href="/admin/posts" class="block px-4 py-2 hover:bg-blue-100 rounded">Посты</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-6 overflow-auto">
        <h1 class="text-2xl font-semibold mb-4">Добро пожаловать в админку!</h1>
        <p>Это главная страница админки.</p>
    </div>
</div>
@endsection