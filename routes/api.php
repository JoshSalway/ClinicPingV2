<?php

use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\SmsController;

Route::get('/patients', [PatientController::class, 'index']);
Route::post('/sms/send', [SmsController::class, 'send']);
Route::get('/patients/{id}', [PatientController::class, 'show']); 