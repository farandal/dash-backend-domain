<?php
        
use Illuminate\Support\Facades\Route;
use Domain\App\Http\Controllers\API\Delivery\TrackingOrder\TrackingOrderController;

// Public routes (no authentication required)
/*
Route::prefix('tracking')->name('tracking.')->group(function () {
    Route::get('{id}', [TrackingOrderController::class, 'publicTracking'])->name('public');
    Route::post('{id}/update', [TrackingOrderController::class, 'updateStatus'])->name('update-status');
});
*/