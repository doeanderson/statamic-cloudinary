<?php

use Illuminate\Support\Facades\Route;
use DoeAnderson\StatamicCloudinary\Http\Controllers\ConfigController;

Route::name('cloudinary.')->prefix('cloudinary')->group(function () {
    Route::name('config.')->prefix('config')->group(function () {
        Route::name('edit')->get('/edit', function () {
            return 'here';
        });

        Route::name('edit')->get('/edit', [ConfigController::class, 'edit']);
        Route::name('update')->post('/edit', [ConfigController::class, 'update']);
    });
});
