<?php

use App\Http\Controllers\Api\PatientController;

Route::get('/patients', [PatientController::class, 'index']); 