<?php

use App\Enums\TokenAbility;
use App\Http\Controllers\Api\AuthController;
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