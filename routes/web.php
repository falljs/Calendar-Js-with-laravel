<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CalendarController;

/* Route::get('/', function () {
    return view('welcome');
}); */
Route::get('/users',[UserController::class,'index'])->name('users.index');
Route::get('/test',[UserController::class,'test']);

//calendar routes
Route::get('calendar/index',[CalendarController::class,'index'])->name('calendar.index');
Route::post('/calendar', [CalendarController::class, 'store'])->name('calendar.store');
Route::patch('/calendar/update/{id}', [CalendarController::class, 'update'])->name('calendar.update');
Route::delete('/calendar/delete/{id}', [CalendarController::class, 'destroy'])->name('calendar.destroy');