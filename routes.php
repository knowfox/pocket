<?php

use Knowfox\Pocket\Controllers\PocketController;

Route::group(['middleware' => 'web'], function () {
    Route::get('pocket', PocketController::class . '@index')->name('pocket');
    Route::get('pocket/auth', PocketController::class . '@auth')->name('pocket.auth');
});