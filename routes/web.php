<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskFileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TaskController::class, 'home'])
    ->name('home')
    ->middleware('auth');

Route::name('auth.')
    ->controller(AuthController::class)
    ->group(function () {
        Route::middleware('guest')->group(function () {
            Route::get('signup', 'signupForm')->name('signupForm');
            Route::post('signup', 'signup')->name('signup');
            Route::get('login', 'loginForm')->name('loginForm');
            Route::post('login', 'login')->name('login');
        });

        Route::middleware('auth')->group(function () {
            Route::post('logout', 'logout')->name('logout');
        });
    });

Route::prefix('tasks')
    ->name('tasks.')
    ->middleware('auth')
    ->group(function () {
        Route::controller(TaskController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{id}/edit', 'edit')->name('edit');
            Route::put('{id}/update', 'update')->name('update');
            Route::get('{id}/delete', 'delete')->name('delete');
            Route::delete('{id}/destroy', 'destroy')->name('destroy');
            Route::get('progress', 'progress')->name('progress');
            Route::patch('{id}/move', 'move')->name('move');
            Route::patch('{id}/complete', 'complete')->name('complete');
        });

        Route::prefix('{task_id}/files')
            ->name('files.')
            ->controller(TaskFileController::class)
            ->group(function () {
                Route::post('store', 'store')->name('store');
                Route::get('{id}/show', 'show')->name('show');
                Route::delete('{id}/destroy', 'destroy')->name('destroy');
            });
    });

Route::prefix('roles')
    ->name('roles.')
    ->middleware('auth')
    ->controller(RoleController::class)
    ->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('{id}/edit', 'edit')->name('edit');
        Route::put('{id}/update', 'update')->name('update');
        Route::get('{id}/delete', 'delete')->name('delete');
        Route::delete('{id}/destroy', 'destroy')->name('destroy');
    });

Route::prefix('users')
    ->name('users.')
    ->middleware('auth')
    ->controller(UserController::class)
    ->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('{id}/edit-role', 'editRole')->name('editRole');
        Route::put('{id}/update-role', 'updateRole')->name('updateRole');
    });
