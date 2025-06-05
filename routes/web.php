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
        $userId = Auth::id();
        $totalPatients = \App\Models\Patient::where('user_id', $userId)->count();
        $formsSentToday = \App\Models\SmsMessage::whereHas('patients', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->whereDate('sent_at', today())->count();
        $todaysAppointments = \App\Models\Patient::where('user_id', $userId)->whereDate('appointment_at', today())->count();

        // Subquery: latest sent_at per patient for this user
        $latestSmsSub = DB::table('patient_sms_message as psm')
            ->select('psm.patient_id', DB::raw('MAX(sm.sent_at) as latest_sent_at'))
            ->join('sms_messages as sm', 'psm.sms_message_id', '=', 'sm.id')
            ->join('patients as p', 'psm.patient_id', '=', 'p.id')
            ->where('p.user_id', $userId)
            ->groupBy('psm.patient_id');

        // Join patients to their latest sms message and count those with status 'pending'
        $pendingForms = DB::table('patients as p')
            ->where('p.user_id', $userId)
            ->joinSub($latestSmsSub, 'latest_sms', function ($join) {
                $join->on('p.id', '=', 'latest_sms.patient_id');
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
