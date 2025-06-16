<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsAdmin;

use App\Http\Controllers\Operator\CategoryController;
use App\Http\Controllers\Operator\UserController;
use App\Http\Controllers\Operator\RoleController;
use App\Http\Controllers\Operator\TaxiController;
use App\Http\Controllers\Operator\FioDtrnController;
use App\Http\Controllers\Operator\ImportFioDtrnController;
use App\Http\Controllers\Operator\FioRipController;


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
    
    Route::resource('categories', CategoryController::class);
    Route::resource('users', UserController::class);
    Route::post('/users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role');
    Route::resource('roles', RoleController::class);
    Route::resource('taxis', TaxiController::class);
    Route::resource('fiodtrns', FioDtrnController::class);
    Route::group(['namespace' => '', 'prefix' => 'import'], function() {
        Route::get('/fiodtrns', [ImportFioDtrnController::class, 'showClientsImportForm'])->name('import.fiodtrns.form');
        Route::post('/fiodtrns', [ImportFioDtrnController::class, 'importClients'])->name('import.fiodtrns.process');
    });
    Route::resource('fio_rips', FioRipController::class)->except(['show']);
    Route::get('fio_rips/{id}/delete', [FioRipController::class, 'destroy'])->name('fio_rips.destroy');
});




Route::get('/clear', function() {   // для очиски кэша сайта
        Artisan::call('cache:clear');    
        Artisan::call('config:cache');    
        Artisan::call('view:clear');  
        Artisan::call('route:clear');  
    return "Кэш очищен.";
})->name('clear');

require __DIR__.'/auth.php';
