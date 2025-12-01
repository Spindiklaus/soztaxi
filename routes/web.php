<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsAdmin;

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TaxiController;
use App\Http\Controllers\Admin\TaxiOrderController; // отправить заказы в такси
use App\Http\Controllers\Admin\TaxiSentOrderController; // отправленные в такси
use App\Http\Controllers\Admin\FioDtrnController;
use App\Http\Controllers\Admin\FioDtrnMergeController;
use App\Http\Controllers\Admin\FioRipController;
use App\Http\Controllers\Admin\SkidkaDopController;
use App\Http\Controllers\Admin\SocialTaxiOrderController;
use App\Http\Controllers\Admin\OrderReportController;
use App\Http\Controllers\Admin\OrderCloseController;
use App\Http\Controllers\Admin\OrderOpenController;
use App\Http\Controllers\Admin\OrderGroupingController;
use App\Http\Controllers\Admin\OrderGroupController;

use App\Http\Controllers\Import\ImportFioDtrnController;
use App\Http\Controllers\Import\ImportFioRipController;
use App\Http\Controllers\Import\ImportCategoryController;
use App\Http\Controllers\Import\ImportOrdersController;
use App\Http\Controllers\Import\ImportTaxiController;

use App\Http\Controllers\Operator\SocialTaxiController;
use App\Http\Controllers\Operator\CarController;
use App\Http\Controllers\Operator\GazelleController;

use App\Http\Controllers\Admin\SocialTaxiOrderExportController;
use App\Http\Controllers\Admin\TaxiOrderImportController;


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
    // Для админимтраторов
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/admin/users', function () {
        return view('admin.users');
    })->name('admin.users');

    // маршрут fiodtrns/merge не будет перехватываться, если поставить вначалк Route::resource('fiodtrns', ...)
    Route::get('/fiodtrns/merge', [FioDtrnMergeController::class, 'showForm'])->name('fiodtrns.merge.form');
    Route::post('/fiodtrns/merge', [FioDtrnMergeController::class, 'merge'])->name('fiodtrns.merge');
    
    Route::resource('categories', CategoryController::class);
    Route::resource('users', UserController::class);
    Route::post('/users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role');
    Route::resource('roles', RoleController::class);
    Route::resource('taxis', TaxiController::class);
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
        // Импорт такси
        Route::get('/taxis/import', [ImportTaxiController::class, 'showImportForm'])->name('import.taxis.form');
        Route::post('/taxis/import', [ImportTaxiController::class, 'import'])->name('import.taxis.process');
    });
    Route::resource('fio_rips', FioRipController::class)->except(['show']);
    Route::resource('skidka_dops', SkidkaDopController::class)->except(['show']);   
    Route::get('/taxi-orders', [TaxiOrderController::class, 'index'])->name('taxi-orders.index');
    Route::get('/taxi_sent-orders', [TaxiSentOrderController::class, 'index'])->name('taxi_sent-orders.index');
    Route::get('/taxi-orders/export-to-taxi', [TaxiOrderController::class, 'exportToTaxi'])->name('taxi-orders.export.to.taxi');
    Route::post('/taxi-orders/set-sent-date', [TaxiOrderController::class, 'setSentDate'])->name('taxi-orders.set-sent-date');    
    Route::patch('/taxi-orders/unset-sent-date', [TaxiSentOrderController::class, 'unsetSentDate'])->name('taxi-orders.unset-sent-date');
    Route::patch('/taxi-orders/transfer-predictive-data', [TaxiSentOrderController::class, 'transferPredictiveData'])->name('taxi-orders.transfer.predictive.data');
    
    Route::get('/reports/orders_visit', [OrderReportController::class, 'index'])->name('orders.report_visit');
    Route::get('/reports/orders_visit/export', [OrderReportController::class, 'export'])->name('orders.report_visit_export');
    
    Route::get('/close', [OrderCloseController::class, 'index'])->name('social-taxi-orders.close.index');
    Route::post('/close', [OrderCloseController::class, 'bulkClose'])->name('social-taxi-orders.close.bulk-close');

    Route::get('/open', [OrderOpenController::class, 'index'])->name('social-taxi-orders.open.index');
    Route::post('/open', [OrderOpenController::class, 'bulkUnset'])->name('social-taxi-orders.open.bulk-unset');

    Route::get('/orders/grouping', [OrderGroupingController::class, 'showGroupingForm'])->name('orders.grouping.form');
    Route::post('/orders/grouping/show', [OrderGroupingController::class, 'showOrdersForGrouping'])->name('orders.grouping.show');
    Route::post('/orders/grouping/process', [OrderGroupingController::class, 'processGrouping'])->name('orders.grouping.process');

    // --- маршрут для удаления заказа из группы --- //
    Route::delete('/order-groups/{orderGroup}/remove-order/{order}', [OrderGroupController::class, 'removeOrderFromGroup'])->name('order-groups.remove-order');
    Route::resource('order-groups', OrderGroupController::class)->names('order-groups'); 
    
    // импорт из такси
    Route::get('/admin/taxi-orders/compare', [TaxiOrderImportController::class, 'showUploadForm'])->name('admin.taxi-orders.compare.form');
    Route::post('/admin/taxi-orders/compare', [TaxiOrderImportController::class, 'compareAndImport'])->name('admin.taxi-orders.compare-and-import');
    
});

Route::middleware(['auth'])->group(function () {
    // Для операторов
    Route::prefix('operator')->name('operator.')->group(function () {
        Route::get('/social-taxi', [SocialTaxiController::class, 'index'])->name('social-taxi.index');
        Route::get('/car', [CarController::class, 'index'])->name('car.index');
        Route::get('/gazelle', [GazelleController::class, 'index'])->name('gazelle.index');
        Route::get('/social-taxi/calendar/client/{client}/date/{date}', [SocialTaxiController::class, 'calendarByClient'])->name('social-taxi.calendar.client');
        Route::post('/social-taxi/copy-order', [SocialTaxiController::class, 'copyOrder'])->name('social-taxi.copy-order');
    });
    Route::resource('fiodtrns', FioDtrnController::class);
    Route::get('/fiodtrns/{fiodtrn}/orders', [FiodtrnController::class, 'showOrders'])->name('fiodtrns.orders');
Route::resource('social-taxi-orders', SocialTaxiOrderController::class)
    ->names('social-taxi-orders')
    ->whereNumber(['social_taxi_order']);    // маршрут для восстановления
    Route::patch('/social-taxi-orders/{social_taxi_order}/restore', [SocialTaxiOrderController::class, 'restore'])->name('social-taxi-orders.restore');
    Route::get('/social-taxi-orders/create/type/{type}', [SocialTaxiOrderController::class, 'createByType'])->name('social-taxi-orders.create.by-type');
    Route::post('/social-taxi-orders/store/type/{type}', [SocialTaxiOrderController::class, 'storeByType'])->name('social-taxi-orders.store.by-type');
    // Отмена заказа - показ формы
    Route::get('/social-taxi-orders/{social_taxi_order}/cancel', [SocialTaxiOrderController::class, 'showCancelForm'])
     ->name('social-taxi-orders.cancel.form');
    // Отмена заказа - выполнение
    Route::patch('/social-taxi-orders/{social_taxi_order}/cancel', [SocialTaxiOrderController::class, 'cancel'])
     ->name('social-taxi-orders.cancel'); 
    // Экспорт в excel
    Route::get('/social-taxi-orders/export', [SocialTaxiOrderExportController::class, 'export'])
    ->name('social-taxi-orders.export');
    
     // Маршруты для возврата заказа из такси ---
    Route::get('/social-taxi-orders/{social_taxi_order}/return-from-taxi', [SocialTaxiOrderController::class, 'showReturnFromTaxiForm'])
         ->name('social-taxi-orders.return-from-taxi.form');
    Route::patch('/social-taxi-orders/{social_taxi_order}/return-from-taxi', [SocialTaxiOrderController::class, 'returnFromTaxi'])
         ->name('social-taxi-orders.return-from-taxi');

});


Route::get('/clear', function() {   // для очиски кэша сайта
        Artisan::call('cache:clear');    
        Artisan::call('config:cache');    
        Artisan::call('view:clear');  
        Artisan::call('route:clear');  
    return "Кэш очищен.";
})->name('clear');

require __DIR__.'/auth.php';
