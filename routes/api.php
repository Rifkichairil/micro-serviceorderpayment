<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Orders
Route::get('orders', [OrderController::class, 'index']);
Route::post('orders', [OrderController::class, 'create']);

// Webhook
Route::post('webhook', [WebhookController::class, 'midtransHandler']);

