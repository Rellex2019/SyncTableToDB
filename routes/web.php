<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [OrderController::class,'index'])->name('orders.index');
Route::prefix('orders')->group(function (){
    Route::post('/generate', [OrderController::class, 'create'])->name('orders.generate');
    Route::delete('/destroy/table', [OrderController::class, 'destroyTable'])->name('orders.destroy-table');
    Route::delete('/destroy/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
});
