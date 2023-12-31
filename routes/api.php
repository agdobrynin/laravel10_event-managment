<?php

use App\Http\Controllers\Api\AttendeeController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('throttle:api')->group(function () {
    Route::get('/user', function(Request $request) {
        return $request->user();
    })
        ->middleware('auth:sanctum');

    Route::post('/take-token', [AuthController::class, 'takeToken'])
        ->name('auth.take-token');

    Route::delete('/invalidate-token', [AuthController::class, 'invalidateToken'])
        ->name('auth.invalidate-token')
        ->middleware('auth:sanctum');
});

Route::apiResource('events', EventController::class);
Route::apiResource('events.attendees', AttendeeController::class)
    ->scoped()
    ->except(['update']);
