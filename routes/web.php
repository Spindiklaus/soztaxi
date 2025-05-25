<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsAdmin;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['web', 'auth', IsAdmin::class])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/admin/users', function () {
        return view('admin.users');
    })->name('admin.users');
});

Route::get('/clear', function() {   // для очиски кэша сайта
        Artisan::call('cache:clear');    
        Artisan::call('config:cache');    
        Artisan::call('view:clear');  
        Artisan::call('route:clear');  
    return "Кэш очищен.";});

require __DIR__.'/auth.php';
