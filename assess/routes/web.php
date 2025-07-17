<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::any('/lti', [\App\Http\Controllers\LTIController::class, 'index'])->name('lti');

