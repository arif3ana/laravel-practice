<?php

use App\Enums\TokenAbility;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/v1/auth/UserLogin', [AuthController::class, 'UserLogin']);
Route::post('/v1/auth/UserRegister', [AuthController::class, 'UserRegister']);

Route::middleware('auth:sanctum', 'ability:' . TokenAbility::ISSUE_ACCESS_TOKEN()->key())->group(function () {
    Route::get('/v1/auth/RefreshToken', [AuthController::class, 'RefreshToken']);
});

Route::middleware('auth:sanctum', 'ability:' . TokenAbility::ACCESS_API()->key())->group(function () {
 Route::get('/v1/transaction/GetTransaction/{limit?}/{offset?}/{type?}', [TransactionController::class, 'GetTransaction']);
});