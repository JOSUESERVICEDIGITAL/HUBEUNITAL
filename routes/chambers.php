<?php

use App\Http\Controllers\ChamberController;
use App\Http\Controllers\DgController;
use App\Http\Controllers\DevelopmentController;
use App\Http\Controllers\EngineeringController;
use App\Http\Controllers\FormationsController;
use App\Http\Controllers\SecretariatController;
use App\Http\Controllers\GraphismeController;
use App\Http\Controllers\JuridiqueController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\StudioController;
use App\Http\Controllers\PublicServiceInboxController;
use App\Http\Controllers\ProspectingFormController;
use App\Http\Middleware\EngineeringAccess;
use App\Http\Middleware\DgAccess;
use App\Http\Middleware\DevelopmentAccess;
use App\Http\Middleware\FormationsAccess;
use App\Http\Middleware\SecretariatAccess;
use App\Http\Middleware\GraphismeAccess;
use App\Http\Middleware\JuridiqueAccess;
use App\Http\Middleware\MarketingAccess;
use App\Http\Middleware\StudioAccess;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'otp.verified'])->group(function () {
    Route::middleware(['verified', 'checkRole:admin,superadmin'])->prefix('backoffice/demandes-services')->name('public-services.')->group(function () {
        Route::get('/', [PublicServiceInboxController::class, 'index'])->name('index');
        Route::get('/{requestId}', [PublicServiceInboxController::class, 'show'])->whereNumber('requestId')->name('show');
        Route::patch('/{requestId}/statut', [PublicServiceInboxController::class, 'updateStatus'])->whereNumber('requestId')->name('status');
    });
    Route::middleware(['verified', 'checkRole:admin,superadmin'])->prefix('backoffice/formulaires')->name('prospecting-forms.')->group(function () {
        Route::get('/', [ProspectingFormController::class, 'index'])->name('index');
        Route::post('/preparer', [ProspectingFormController::class, 'resolve'])->name('resolve');
        Route::post('/enregistrer', [ProspectingFormController::class, 'store'])->name('store');
        Route::get('/{prospectId}/pdf', [ProspectingFormController::class, 'downloadPdf'])->whereNumber('prospectId')->name('pdf');
        Route::get('/pdf-archives/{downloadId}', [ProspectingFormController::class, 'downloadArchivedPdf'])->whereNumber('downloadId')->name('pdf-archive');
        Route::get('/{prospectId}', [ProspectingFormController::class, 'show'])->whereNumber('prospectId')->name('show');
    });

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
    Route::prefix('developpement')->name('development.')->middleware(DevelopmentAccess::class)->group(function () {
        Route::get('/', [DevelopmentController::class, 'dashboard'])->name('dashboard');
        Route::get('/rapports', [DevelopmentController::class, 'reports'])->name('reports');
        Route::get('/historique', [DevelopmentController::class, 'auditLog'])->name('audit');
        Route::get('/{module}/export', [DevelopmentController::class, 'export'])->name('export');
        Route::get('/{module}/nouveau', [DevelopmentController::class, 'create'])->name('create');
        Route::get('/{module}', [DevelopmentController::class, 'index'])->name('index');
        Route::post('/{module}', [DevelopmentController::class, 'store'])->name('store');
        Route::get('/{module}/{record}/modifier', [DevelopmentController::class, 'edit'])->whereNumber('record')->name('edit');
        Route::get('/{module}/{record}', [DevelopmentController::class, 'show'])->whereNumber('record')->name('show');
        Route::put('/{module}/{record}', [DevelopmentController::class, 'update'])->whereNumber('record')->name('update');
        Route::delete('/{module}/{record}', [DevelopmentController::class, 'destroy'])->whereNumber('record')->name('destroy');
    });
    Route::prefix('formations')->name('formations.')->middleware(FormationsAccess::class)->group(function () {
        Route::get('/', [FormationsController::class, 'dashboard'])->name('dashboard');
        Route::get('/rapports', [FormationsController::class, 'reports'])->name('reports');
        Route::get('/historique', [FormationsController::class, 'auditLog'])->name('audit');
        Route::get('/{module}/export', [FormationsController::class, 'export'])->name('export');
        Route::get('/content/{record}/telecharger', [FormationsController::class, 'downloadContent'])->whereNumber('record')->name('content.download');
        Route::get('/{module}/nouveau', [FormationsController::class, 'create'])->name('create');
        Route::get('/{module}', [FormationsController::class, 'index'])->name('index');
        Route::post('/{module}', [FormationsController::class, 'store'])->name('store');
        Route::get('/{module}/{record}/modifier', [FormationsController::class, 'edit'])->whereNumber('record')->name('edit');
        Route::get('/{module}/{record}', [FormationsController::class, 'show'])->whereNumber('record')->name('show');
        Route::put('/{module}/{record}', [FormationsController::class, 'update'])->whereNumber('record')->name('update');
        Route::delete('/{module}/{record}', [FormationsController::class, 'destroy'])->whereNumber('record')->name('destroy');
    });
    Route::prefix('secretariat')->name('secretariat.')->middleware(SecretariatAccess::class)->group(function () {
        Route::get('/', [SecretariatController::class, 'dashboard'])->name('dashboard');
        Route::get('/rapports', [SecretariatController::class, 'reports'])->name('reports');
        Route::get('/historique', [SecretariatController::class, 'auditLog'])->name('audit');
        Route::get('/{module}/export', [SecretariatController::class, 'export'])->name('export');
        Route::get('/{module}/nouveau', [SecretariatController::class, 'create'])->name('create');
        Route::get('/{module}', [SecretariatController::class, 'index'])->name('index');
        Route::post('/{module}', [SecretariatController::class, 'store'])->name('store');
        Route::get('/{module}/{record}/modifier', [SecretariatController::class, 'edit'])->whereNumber('record')->name('edit');
        Route::get('/{module}/{record}', [SecretariatController::class, 'show'])->whereNumber('record')->name('show');
        Route::put('/{module}/{record}', [SecretariatController::class, 'update'])->whereNumber('record')->name('update');
        Route::delete('/{module}/{record}', [SecretariatController::class, 'destroy'])->whereNumber('record')->name('destroy');
    });
    Route::prefix('dg-partenariats')->name('dg.')->middleware(DgAccess::class)->group(function () {
        Route::get('/', [DgController::class, 'dashboard'])->name('dashboard');
        Route::get('/rapports', [DgController::class, 'reports'])->name('reports');
        Route::get('/historique', [DgController::class, 'auditLog'])->name('audit');
        Route::get('/{module}/export', [DgController::class, 'export'])->name('export');
        Route::get('/{module}/nouveau', [DgController::class, 'create'])->name('create');
        Route::get('/{module}', [DgController::class, 'index'])->name('index');
        Route::post('/{module}', [DgController::class, 'store'])->name('store');
        Route::get('/{module}/{record}/modifier', [DgController::class, 'edit'])->whereNumber('record')->name('edit');
        Route::get('/{module}/{record}', [DgController::class, 'show'])->whereNumber('record')->name('show');
        Route::put('/{module}/{record}', [DgController::class, 'update'])->whereNumber('record')->name('update');
        Route::delete('/{module}/{record}', [DgController::class, 'destroy'])->whereNumber('record')->name('destroy');
    });
});
