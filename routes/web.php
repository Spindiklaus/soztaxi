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
use App\Http\Controllers\Operator\ImportFioRipController;
use App\Http\Controllers\Operator\SkidkaDopController;
use App\Http\Controllers\Operator\SocialTaxiOrderController;
use App\Http\Controllers\Operator\ImportCategoryController;
use App\Http\Controllers\Operator\ImportOrdersController;



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
        Route::get('/fio_rips', [ImportFioRipController::class, 'showImportForm'])->name('import.fio_rips.form');
        Route::post('/fio_rips', [ImportFioRipController::class, 'import'])->name('import.fio_rips.process');    
        // Форма импорта категории
        Route::get('/categories', [ImportCategoryController::class, 'showImportForm'])->name('import.categories.form');
        // Обработка загрузки CSV
        Route::post('/categories', [ImportCategoryController::class, 'import'])->name('import.categories.process');        
        // Импорт заказов
        Route::get('/orders', [ImportOrdersController::class, 'showImportForm'])->name('import.orders.form');
        Route::post('/orders', [ImportOrdersController::class, 'import'])->name('import.orders.process');        
    });
    Route::resource('fio_rips', FioRipController::class)->except(['show']);
    Route::resource('skidka_dops', SkidkaDopController::class)->except(['show']);
    Route::resource('social-taxi-orders', SocialTaxiOrderController::class)->names('social-taxi-orders'); 
});

Route::get('/clear', function() {   // для очиски кэша сайта
        Artisan::call('cache:clear');    
        Artisan::call('config:cache');    
        Artisan::call('view:clear');  
        Artisan::call('route:clear');  
    return "Кэш очищен.";
})->name('clear');

require __DIR__.'/auth.php';
