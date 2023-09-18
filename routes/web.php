<?php

use App\Http\Controllers\HttpTestController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//Route::get('/test', HttpTestController::class);

Route::get('/', function () {
    return Inertia::render('Index');
});