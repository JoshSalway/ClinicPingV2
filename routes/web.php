<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $totalPatients = Patient::count();
        $formsSentToday = DB::table('sms_messages')->whereDate('sent_at', today())->count();
        $pendingForms = Patient::where(function ($q) {
            $q->whereHas('latestSmsMessage', function ($q2) {
                $q2->where('status', 'pending');
            })->orWhereDoesntHave('latestSmsMessage');
        })->count();
        $todaysAppointments = Patient::whereDate('appointment_at', today())->count();

        return Inertia::render('dashboard', [
            'totalPatients' => $totalPatients,
            'formsSentToday' => $formsSentToday,
            'pendingForms' => $pendingForms,
            'todaysAppointments' => $todaysAppointments,
        ]);
    })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
