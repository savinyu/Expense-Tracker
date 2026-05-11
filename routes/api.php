<?php

use App\Http\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('expenses', ExpenseController::class)
        ->only(['index', 'store', 'update', 'destroy']);
});
