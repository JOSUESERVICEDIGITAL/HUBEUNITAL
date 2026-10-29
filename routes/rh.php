<?php

use App\Http\Controllers\RhController;
use App\Http\Middleware\RhAccess;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'otp.verified', RhAccess::class])->prefix('rh')->name('rh.')->group(function () {
    Route::get('/', [RhController::class, 'dashboard'])->name('dashboard');
    Route::get('/rapports', [RhController::class, 'reports'])->name('reports');
    Route::get('/historique', [RhController::class, 'auditLog'])->name('audit');
    Route::get('/acces', [RhController::class, 'accessIndex'])->name('access');
    Route::post('/acces', [RhController::class, 'accessStore'])->name('access.store');
    Route::post('/candidatures/{record}/integrer', [RhController::class, 'hire'])->whereNumber('record')->name('hire');
    Route::post('/paie/{record}/transmettre', [RhController::class, 'transmit'])->whereNumber('record')->name('transmit');
    Route::get('/{module}/export', [RhController::class, 'export'])->name('export');
    Route::get('/{module}/nouveau', [RhController::class, 'create'])->name('create');
    Route::get('/{module}', [RhController::class, 'index'])->name('index');
    Route::post('/{module}', [RhController::class, 'store'])->name('store');
    Route::get('/{module}/{record}/piece', [RhController::class, 'download'])->whereNumber('record')->name('download');
    Route::get('/{module}/{record}/modifier', [RhController::class, 'edit'])->whereNumber('record')->name('edit');
    Route::get('/{module}/{record}', [RhController::class, 'show'])->whereNumber('record')->name('show');
    Route::put('/{module}/{record}', [RhController::class, 'update'])->whereNumber('record')->name('update');
});
