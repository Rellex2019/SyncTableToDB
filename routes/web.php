<?php

use App\Http\Controllers\OrderController;
use App\Services\GoogleSheetsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [OrderController::class,'index'])->name('orders.index');
Route::prefix('orders')->group(function (){
    Route::post('/generate', [OrderController::class, 'create'])->name('orders.generate');
    Route::delete('/destroy/table', [OrderController::class, 'destroyTable'])->name('orders.destroy-table');
    Route::delete('/destroy/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
    Route::post('/set-sheet', [OrderController::class, 'setSheet'])->name('orders.set-sheet');
});

Route::get('/auth/google', function (GoogleSheetsService $sheets) {
    return redirect($sheets->getAuthUrl());
});

Route::get('/auth/callback', function (Request $request, GoogleSheetsService $sheets) {
    $sheets->handleCallback($request->code);
    return redirect('/')->with('success', 'Успешная авторизация!');
});

Route::get('/fetch/{count?}', [OrderController::class, 'fetchOrders'])
    ->where('count', '\d+')
    ->name('sheet.fetch');