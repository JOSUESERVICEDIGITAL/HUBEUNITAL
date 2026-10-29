<?php

use App\Http\Controllers\CommercialController;
use App\Http\Middleware\CommercialAccess;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'otp.verified', CommercialAccess::class])->prefix('commerciale')->name('commercial.')->group(function () {
    Route::get('/', [CommercialController::class, 'dashboard'])->name('dashboard');
    Route::get('/statistiques', [CommercialController::class, 'statistics'])->name('statistics');
    Route::get('/pipeline', [CommercialController::class, 'pipeline'])->name('pipeline');
    Route::get('/agenda', [CommercialController::class, 'agenda'])->name('agenda');
    Route::patch('/opportunites/{record}/etape', [CommercialController::class, 'moveOpportunity'])->whereNumber('record')->name('pipeline.move');
    Route::get('/disponibilites-rh', [CommercialController::class, 'availability'])->name('availability');
    Route::post('/conges/{record}/avis', [CommercialController::class, 'leaveOpinion'])->whereNumber('record')->name('leave.opinion');
    Route::get('/cartographie', [CommercialController::class, 'map'])->name('map');
    Route::get('/transmissions', [CommercialController::class, 'handoffs'])->name('handoffs');
    Route::get('/historique', [CommercialController::class, 'auditLog'])->name('audit');
    Route::get('/acces', [CommercialController::class, 'accessIndex'])->name('access');
    Route::post('/acces', [CommercialController::class, 'accessStore'])->name('access.store');
    Route::post('/prospects/{record}/convertir', [CommercialController::class, 'convert'])->whereNumber('record')->name('convert');
    Route::post('/besoins/{record}/etape', [CommercialController::class, 'advance'])->whereNumber('record')->name('advance');
    Route::post('/contrats/{record}/avis', [CommercialController::class, 'review'])->whereNumber('record')->name('review');
    Route::post('/commissions/{record}/valider', [CommercialController::class, 'approveCommission'])->whereNumber('record')->name('commission.approve');
    Route::post('/factures/{record}/paiement', [CommercialController::class, 'payment'])->whereNumber('record')->name('payment');
    Route::get('/{module}/export', [CommercialController::class, 'export'])->name('export');
    Route::get('/{module}/nouveau', [CommercialController::class, 'create'])->name('create');
    Route::get('/{module}', [CommercialController::class, 'index'])->name('index');
    Route::post('/{module}', [CommercialController::class, 'store'])->name('store');
    Route::get('/{module}/{record}/modifier', [CommercialController::class, 'edit'])->whereNumber('record')->name('edit');
    Route::get('/{module}/{record}/imprimer', [CommercialController::class, 'printDocument'])->whereNumber('record')->name('print');
    Route::patch('/{module}/{record}/archiver', [CommercialController::class, 'archive'])->whereNumber('record')->name('archive');
    Route::post('/{module}/{record}/documents', [CommercialController::class, 'uploadDocument'])->whereNumber('record')->name('documents.store');
    Route::get('/{module}/{record}/documents/{document}', [CommercialController::class, 'downloadDocument'])->whereNumber(['record', 'document'])->name('documents.download');
    Route::get('/{module}/{record}', [CommercialController::class, 'show'])->whereNumber('record')->name('show');
    Route::put('/{module}/{record}', [CommercialController::class, 'update'])->whereNumber('record')->name('update');
});
