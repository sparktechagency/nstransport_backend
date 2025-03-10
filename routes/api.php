<?php

use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\VehicleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::resource('category',CategoryController::class);
Route::resource('vehicle',VehicleController::class);
