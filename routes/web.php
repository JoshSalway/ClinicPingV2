<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use App\Models\SmsMessage;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $user = Auth::user();
        $userId = $user->id;
        $tz = $user->timezone ?? 'Australia/Melbourne';
        $today = \Carbon\Carbon::now($tz)->format('Y-m-d');
        $totalPatients = \App\Models\Patient::where('user_id', $userId)->count();
        // Only count SMS sent today for patients with an appointment today
        $formsSentToday = \App\Models\Patient::where('user_id', $userId)
            ->whereDate('appointment_at', $today)
            ->whereHas('smsMessages', function($q) use ($today) {
                $q->whereDate('sent_at', $today);
            })
            ->count();
        $todaysAppointments = \App\Models\Patient::where('user_id', $userId)->whereDate('appointment_at', $today)->count();

        // Pending forms: patients with appointment today and either:
        // 1. No SMS sent, or
        // 2. SMS sent but no form submission exists or form is in_progress
        $pendingForms = \App\Models\Patient::where('user_id', $userId)
            ->whereDate('appointment_at', $today)
            ->where(function($q) {
                $q->whereDoesntHave('smsMessages')
                  ->orWhereHas('smsMessages', function($q2) {
                      $q2->latest('sent_at')->whereNull('sent_at');
                  })
                  ->orWhereDoesntHave('formSubmissions')
                  ->orWhereHas('formSubmissions', function($q2) {
                      $q2->where('status', 'in_progress');
                  });
            })
            ->count();

        return Inertia::render('dashboard', [
            'totalPatients' => $totalPatients,
            'formsSentToday' => $formsSentToday,
            'pendingForms' => $pendingForms,
            'todaysAppointments' => $todaysAppointments,
        ]);
    })->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/settings/reset-demo-data', [\App\Http\Controllers\Settings\ProfileController::class, 'resetDemoData'])->name('settings.reset-demo-data');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
