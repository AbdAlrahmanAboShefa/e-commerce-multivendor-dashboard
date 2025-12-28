<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/admin/test', function () {
    return 'Admin OK';
})->middleware(['auth', 'role:admin']);




