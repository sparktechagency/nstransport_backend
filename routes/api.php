<?php

use App\Http\Controllers\api\BookingController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\HomeController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\VehicleController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum', 'admin')->prefix('admin')->group(function () {
    Route::apiResource('user', UserController::class);
});

Route::middleware('auth:sanctum', 'admin.user')->group(function () {
    Route::resource('category', CategoryController::class);
    Route::post('booking', [BookingController::class, 'booking']);
    Route::delete('booking-cancle/{id}', [BookingController::class, 'bookingCancle']);
    Route::put('booking-update/{id}', [BookingController::class, 'bookingUpdate']);
    Route::put('customer-update/{customer_id}', [BookingController::class, 'multipleBookingUpdate']);
    Route::get('vehicle-booking-list/{id}', [BookingController::class, 'vehicleBookingList']);
    Route::resource('vehicle', VehicleController::class);







    Route::get('statistic', [HomeController::class, 'statistic']);
    Route::get('search', [HomeController::class, 'search']);
    Route::get('search_by_type', [HomeController::class, 'searchByType']);
});
