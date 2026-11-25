<?php

use App\Http\Controllers\AeronaveController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserController;
use App\Models\Aeronave;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'publicAeronaves' => Aeronave::query()
            ->publicas()
            ->with('publicacao')
            ->latest('id')
            ->take(3)
            ->get(),
    ]);
})->name('landing');

Route::post('/login', [SessionController::class, 'store'])
    ->middleware(['guest', 'throttle:5,1'])
    ->name('login');
Route::post('/cadastro', [RegistrationController::class, 'store'])
    ->middleware(['guest', 'throttle:5,1'])
    ->name('register');
Route::post('/logout', [SessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::resource('aeronaves', AeronaveController::class)
        ->parameters(['aeronaves' => 'aeronave']);
    Route::resource('papeis', RoleController::class)
        ->parameters(['papeis' => 'role'])
        ->names('roles')
        ->except('show');
    Route::resource('usuarios', UserController::class)
        ->parameters(['usuarios' => 'user'])
        ->names('users')
        ->except('show');
});
