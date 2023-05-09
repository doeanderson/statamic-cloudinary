<?php

use DoeAnderson\StatamicCloudinary\Http\Controllers\ConfigController;
use Illuminate\Support\Facades\Route;

Route::name('cloudinary.')->prefix('cloudinary')->group(function () {
    Route::name('config.')->prefix('config')->group(function () {
        Route::name('edit')->get('/edit', [ConfigController::class, 'edit']);
        Route::name('update')->post('/edit', [ConfigController::class, 'update']);
    });
});
