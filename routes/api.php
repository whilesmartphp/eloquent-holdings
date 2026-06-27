<?php

use Illuminate\Support\Facades\Route;
use Whilesmart\Holdings\Http\Controllers\HoldingController;

Route::post('holdings/reprice', [HoldingController::class, 'reprice']);
Route::apiResource('holdings', HoldingController::class);
