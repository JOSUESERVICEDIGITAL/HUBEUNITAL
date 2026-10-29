<?php

use App\Http\Controllers\ChamberController;
use App\Http\Controllers\EngineeringController;
use App\Http\Controllers\GraphismeController;
use App\Http\Controllers\JuridiqueController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\StudioController;
use App\Http\Middleware\EngineeringAccess;
use App\Http\Middleware\GraphismeAccess;
use App\Http\Middleware\JuridiqueAccess;
use App\Http\Middleware\MarketingAccess;
use App\Http\Middleware\StudioAccess;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'otp.verified'])->group(function () {
    Route::prefix('juridique')->name('juridique.')->middleware(JuridiqueAccess::class)->group(function () {
        Route::get('/', [JuridiqueController::class, 'dashboard'])->name('dashboard');
        Route::get('/rapports', [JuridiqueController::class, 'reports'])->name('reports');
        Route::get('/historique', [JuridiqueController::class, 'auditLog'])->name('audit');
        Route::get('/{module}/export', [JuridiqueController::class, 'export'])->name('export');
        Route::get('/{module}/nouveau', [JuridiqueController::class, 'create'])->name('create');
        Route::get('/{module}', [JuridiqueController::class, 'index'])->name('index');
        Route::post('/{module}', [JuridiqueController::class, 'store'])->name('store');
        Route::get('/{module}/{record}/modifier', [JuridiqueController::class, 'edit'])->whereNumber('record')->name('edit');
        Route::get('/{module}/{record}', [JuridiqueController::class, 'show'])->whereNumber('record')->name('show');
        Route::put('/{module}/{record}', [JuridiqueController::class, 'update'])->whereNumber('record')->name('update');
        Route::delete('/{module}/{record}', [JuridiqueController::class, 'destroy'])->whereNumber('record')->name('destroy');
    });
    Route::prefix('graphisme')->name('graphisme.')->middleware(GraphismeAccess::class)->group(function () {
        Route::get('/', [GraphismeController::class, 'dashboard'])->name('dashboard');
        Route::get('/rapports', [GraphismeController::class, 'reports'])->name('reports');
        Route::get('/historique', [GraphismeController::class, 'auditLog'])->name('audit');
        Route::get('/galerie', [GraphismeController::class, 'gallery'])->name('gallery');
        Route::get('/planning', [GraphismeController::class, 'planning'])->name('planning');
        Route::get('/{module}/export', [GraphismeController::class, 'export'])->name('export');
        Route::get('/{module}/nouveau', [GraphismeController::class, 'create'])->name('create');
        Route::get('/{module}', [GraphismeController::class, 'index'])->name('index');
        Route::post('/{module}', [GraphismeController::class, 'store'])->name('store');
        Route::get('/{module}/{record}/modifier', [GraphismeController::class, 'edit'])->whereNumber('record')->name('edit');
        Route::get('/{module}/{record}', [GraphismeController::class, 'show'])->whereNumber('record')->name('show');
        Route::put('/{module}/{record}', [GraphismeController::class, 'update'])->whereNumber('record')->name('update');
        Route::delete('/{module}/{record}', [GraphismeController::class, 'destroy'])->whereNumber('record')->name('destroy');
    });
    Route::prefix('studio')->name('studio.')->middleware(StudioAccess::class)->group(function () {
        Route::get('/', [StudioController::class, 'dashboard'])->name('dashboard');
        Route::get('/rapports', [StudioController::class, 'reports'])->name('reports');
        Route::get('/historique', [StudioController::class, 'auditLog'])->name('audit');
        Route::get('/planning', [StudioController::class, 'planning'])->name('planning');
        Route::get('/{module}/export', [StudioController::class, 'export'])->name('export');
        Route::get('/{module}/nouveau', [StudioController::class, 'create'])->name('create');
        Route::get('/{module}', [StudioController::class, 'index'])->name('index');
        Route::post('/{module}', [StudioController::class, 'store'])->name('store');
        Route::get('/{module}/{record}/modifier', [StudioController::class, 'edit'])->whereNumber('record')->name('edit');
        Route::get('/{module}/{record}', [StudioController::class, 'show'])->whereNumber('record')->name('show');
        Route::put('/{module}/{record}', [StudioController::class, 'update'])->whereNumber('record')->name('update');
        Route::delete('/{module}/{record}', [StudioController::class, 'destroy'])->whereNumber('record')->name('destroy');
    });
    Route::prefix('marketing')->name('marketing.')->middleware(MarketingAccess::class)->group(function () {
        Route::get('/', [MarketingController::class, 'dashboard'])->name('dashboard');
        Route::get('/rapports', [MarketingController::class, 'reports'])->name('reports');
        Route::get('/historique', [MarketingController::class, 'auditLog'])->name('audit');
        Route::get('/calendrier', [MarketingController::class, 'calendar'])->name('calendar');
        Route::get('/pipeline', [MarketingController::class, 'pipeline'])->name('pipeline');
        Route::post('/leads/{record}/convertir', [MarketingController::class, 'convertLead'])->whereNumber('record')->name('convert');
        Route::get('/{module}/export', [MarketingController::class, 'export'])->name('export');
        Route::get('/{module}/nouveau', [MarketingController::class, 'create'])->name('create');
        Route::get('/{module}', [MarketingController::class, 'index'])->name('index');
        Route::post('/{module}', [MarketingController::class, 'store'])->name('store');
        Route::get('/{module}/{record}/modifier', [MarketingController::class, 'edit'])->whereNumber('record')->name('edit');
        Route::get('/{module}/{record}', [MarketingController::class, 'show'])->whereNumber('record')->name('show');
        Route::put('/{module}/{record}', [MarketingController::class, 'update'])->whereNumber('record')->name('update');
        Route::delete('/{module}/{record}', [MarketingController::class, 'destroy'])->whereNumber('record')->name('destroy');
    });
    Route::get('/chambres', [ChamberController::class, 'index'])->name('chambers.index');
    Route::get('/chambres/{chamber}', [ChamberController::class, 'show'])->name('chambers.show');
    Route::patch('/chambres/{chamber}/statut', [ChamberController::class, 'updateStatus'])->name('chambers.status');

    Route::prefix('ingenierie')->name('engineering.')->middleware(EngineeringAccess::class)->group(function () {
        Route::get('/', [EngineeringController::class, 'dashboard'])->name('dashboard');
        Route::get('/rapports', [EngineeringController::class, 'reports'])->name('reports');
        Route::get('/historique', [EngineeringController::class, 'auditLog'])->name('audit');
        Route::get('/{module}/export', [EngineeringController::class, 'export'])->name('export');
        Route::get('/{module}/nouveau', [EngineeringController::class, 'create'])->name('create');
        Route::get('/{module}', [EngineeringController::class, 'index'])->name('index');
        Route::post('/{module}', [EngineeringController::class, 'store'])->name('store');
        Route::get('/{module}/{record}/modifier', [EngineeringController::class, 'edit'])->whereNumber('record')->name('edit');
        Route::get('/{module}/{record}', [EngineeringController::class, 'show'])->whereNumber('record')->name('show');
        Route::put('/{module}/{record}', [EngineeringController::class, 'update'])->whereNumber('record')->name('update');
        Route::delete('/{module}/{record}', [EngineeringController::class, 'destroy'])->whereNumber('record')->name('destroy');
    });
});
