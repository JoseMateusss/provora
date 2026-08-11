<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Redireciona /api/documentation para a interface interativa do Scramble (/docs/api) conforme SPEC 00 / SPEC 07
Route::get('/api/documentation', function () {
    return redirect('/docs/api');
});
