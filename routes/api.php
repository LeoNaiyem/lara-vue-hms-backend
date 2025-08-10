<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\BedController;
use App\Http\Controllers\Api\ConsultantController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\DesignationController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\MedicineCategoryController;
use App\Http\Controllers\Api\MedicineController;
use App\Http\Controllers\Api\MedicineTypeController;
use App\Http\Controllers\Api\MoneyReceiptController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\PrescriptionController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\WardController;
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
Route::apiResource('wards',WardController::class);
Route::apiResource('medicines',MedicineController::class);
Route::apiResource('medicine-types',MedicineTypeController::class);
Route::apiResource('invoices',InvoiceController::class);
Route::apiResource('money-receipts',MoneyReceiptController::class);
Route::apiResource('prescriptions', PrescriptionController::class);
Route::apiResource('consultants',ConsultantController::class);
