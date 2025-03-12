<?php

use App\Http\Controllers\api\BookingController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\HomeController;
use App\Http\Controllers\api\VehicleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::get('statistic', [HomeController::class, 'statistic']);
Route::post('booking', [BookingController::class, 'booking']);
Route::get('search', [HomeController::class, 'search']);
Route::get('search_by_type', [HomeController::class, 'searchByType']);
Route::get('total_vehicle', [HomeController::class, 'totalVehicle']);

Route::resource('category', CategoryController::class);
Route::resource('vehicle', VehicleController::class);
