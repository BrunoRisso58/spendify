<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\RecurrenceController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReportController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Transactions
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::post('/', [TransactionController::class, 'store']);
        Route::get('/{id}', [TransactionController::class, 'show']);
        Route::put('/{id}', [TransactionController::class, 'update']);
        Route::delete('/{id}', [TransactionController::class, 'destroy']);
    });

    // Recurrences
    Route::prefix('recurrences')->group(function () {
        Route::get('/', [RecurrenceController::class, 'index']);
        Route::post('/', [RecurrenceController::class, 'store']);
        Route::put('/{id}', [RecurrenceController::class, 'update']);
        Route::delete('/{id}', [RecurrenceController::class, 'destroy']);
        Route::patch('/{id}/toggle', [RecurrenceController::class, 'toggle']);
    });

    // Categories
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/me', [CategoryController::class, 'getByAuthenticatedUser']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
    });

    // Dashboard
    Route::prefix('reports')->group(function () {
        Route::get('/summary', [ReportController::class, 'summary']);
        Route::get('/categories', [ReportController::class, 'categories']);
        Route::get('/cashflow', [ReportController::class, 'cashflow']);
    });
});