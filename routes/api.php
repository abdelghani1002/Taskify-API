<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\CompleteTaskController;
use App\Http\Controllers\API\InCompleteTaskController;
use App\Http\Controllers\API\TaskController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
});

Route::apiResource("tasks", TaskController::class);
Route::post('tasks/complete/{task}', CompleteTaskController::class)->middleware('auth:api');
Route::post('tasks/incomplete/{task}', InCompleteTaskController::class)->middleware('auth:api');
