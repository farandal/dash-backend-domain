<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Logs\LogController;

Route::group(['middleware' => array_filter(['access', 'auth:sanctum', app()->environment('production') ? 'verified' : null]), 'as' => 'app.', 'prefix' => 'app'], function () {


    Route::prefix('logs')->name('logs.')->group(function () {
        $class = LogController::class;
        $RAMethods = config('react-admin-methods');
        foreach ($RAMethods as $methodName => $methodValues) {
            if (in_array($methodValues['controllerMethod'], ['create', 'update'])) {
                continue;
            }
            Route::{$methodValues['method']}($methodValues['path'], [$class, $methodValues['controllerMethod']])
                ->middleware("ControllerOptions:mode/" . $methodValues['mode'])
                ->name($methodName);
        }
        Route::get('/{log}/download', [$class, 'download'])->name('download');
    });


});
