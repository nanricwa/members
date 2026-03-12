<?php

use App\Http\Controllers\Payment\WebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/stripe', [WebhookController::class, 'handleStripe'])->name('webhook.stripe');
// Phase 3で追加:
// Route::post('/square', [WebhookController::class, 'handleSquare'])->name('webhook.square');
// Route::post('/univapay', [WebhookController::class, 'handleUnivaPay'])->name('webhook.univapay');
