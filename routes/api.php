<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MonitorController;

Route::middleware('referer')->group(function () {
    Route::get('monitor', MonitorController::class);
});