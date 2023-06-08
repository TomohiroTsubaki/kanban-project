<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::prefix('tasks')
    ->name('tasks.')
    ->controller(TaskController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');
    });

Route::prefix('users')
    ->controller(UserController::class)
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/create', 'create');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::get('/{id}/edit', 'edit');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });
