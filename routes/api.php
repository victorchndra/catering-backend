<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/catering-package/{cateringPackage:slug}', [CateringPackageController::class, 'show']);
Route::apiResource('/catering-packages', CateringPackageController::class);

Route::get('/filters/catering-packages', [CateringController::class, 'filterPackages']);

Route::get('/category/{category:slug}', [CategoryController::class, 'show']);
Route::apiResource('/categories', CategoryController::class);

Route::get('/category/{category:slug}', [CategoryController::class, 'show']);
Route::apiResource('/categories', CategoryController::class);

Route::get('/city/{city:slug}', [CityController::class, 'show']);
Route::apiResource('/cities', CityController::class);

Route::apiResource('/testimonials', CateringTestimonialController::class);

Route::post('/booking-transaction', [CateringSubscriptionController::class, 'store']);
Route::post('/check-booking', [CateringSubscriptionController::class, 'booking_details']);
