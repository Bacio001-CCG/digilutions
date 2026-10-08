<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationQuoteController;
use App\Http\Controllers\ReservationReturnController;
use App\Http\Controllers\TodayController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TodayController::class, 'index'])->name('home');
Route::get('vandaag', [TodayController::class, 'index'])->name('vandaag');
Route::get('dashboard', [TodayController::class, 'index'])->name('dashboard');

Route::resource('machines', MachineController::class)
    ->only(['index', 'create', 'store', 'show']);

Route::resource('klanten', CustomerController::class)
    ->parameters(['klanten' => 'customer']);

Route::get('reserveringen', [ReservationController::class, 'index'])->name('reserveringen.index');
Route::get('reservering', [ReservationController::class, 'create'])->name('reservering');
Route::post('reserveringen', [ReservationController::class, 'store'])->name('reserveringen.store');
Route::post('reserveringen/prijs', ReservationQuoteController::class)->name('reserveringen.quote');
Route::post('reserveringen/{reservation}/retour', ReservationReturnController::class)
    ->name('reserveringen.retour');
Route::delete('reserveringen/{reservation}', [ReservationController::class, 'destroy'])
    ->name('reserveringen.destroy');

require __DIR__.'/settings.php';
