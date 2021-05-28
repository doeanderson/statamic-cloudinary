<?php

use Illuminate\Support\Facades\Route;

Route::name('cloudinary.')->prefix('cloudinary')->group(function () {
    Route::name('config.')->prefix('config')->group(function () {
        Route::name('edit')->get('/edit', 'ConfigController@edit');
        Route::name('update')->post('/edit', 'ConfigController@update');
    });
});
