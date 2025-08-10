<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\BedController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\DesignationController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\MedicineCategoryController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('appointments', AppointmentController::class);
Route::apiResource('doctors', DoctorController::class);
Route::apiResource('patients', PatientController::class);
Route::apiResource('departments', DepartmentController::class);
Route::apiResource('designations',DesignationController::class);
Route::apiResource('services',ServiceController::class);
Route::apiResource('medicine-categories',MedicineCategoryController::class);
Route::apiResource('beds',BedController::class);
