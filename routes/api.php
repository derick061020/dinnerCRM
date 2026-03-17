<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\WooOrderController;


Route::match(['get','post'], '/wordpress-availability', [AvailabilityController::class, 'getWordpressAvailability']);
Route::match(['get','post'], '/day-slots', [AvailabilityController::class, 'getSingleDayAvailability']);
Route::post('/woo-order-webhook', [WooOrderController::class,'syncWebhook']);
Route::get('/orders/{woocommerce_order_id}', [WooOrderController::class,'getOrderDetails']);