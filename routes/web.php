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
        // Only count SMS with status 'sent' for today AND for patients with an appointment today
        $formsSentToday = \App\Models\SmsMessage::whereHas('patients', function($q) use ($userId, $today) {
            $q->where('user_id', $userId)
              ->whereDate('appointment_at', $today);
        })->where('status', 'sent')->whereDate('sent_at', $today)->count();
        $todaysAppointments = \App\Models\Patient::where('user_id', $userId)->whereDate('appointment_at', $today)->count();

        // Subquery: latest sms per patient for this user, for patients with appointment today
        $latestSmsSub = DB::table('patient_sms_message as psm')
            ->select('psm.patient_id', DB::raw('MAX(sm.sent_at) as latest_sent_at'))
            ->join('sms_messages as sm', 'psm.sms_message_id', '=', 'sm.id')
            ->join('patients as p', 'psm.patient_id', '=', 'p.id')
            ->where('p.user_id', $userId)
            ->whereDate('p.appointment_at', $today)
            ->groupBy('psm.patient_id');

        // Join patients to their latest sms message and count those with status 'pending'
        $pendingForms = DB::table('patients as p')
            ->where('p.user_id', $userId)
            ->whereDate('p.appointment_at', $today)
            ->joinSub($latestSmsSub, 'latest_sms', function ($join) {
                $join->on('p.id', '=', 'latest_sms.patient_id');
            })
            ->join('patient_sms_message as psm', function($join) {
                $join->on('psm.patient_id', '=', 'p.id')
                     ->on('psm.sms_message_id', '=', DB::raw('(SELECT id FROM sms_messages WHERE sms_messages.sent_at = latest_sms.latest_sent_at AND sms_messages.id = psm.sms_message_id LIMIT 1)'));
            })
            ->join('sms_messages as sm', 'psm.sms_message_id', '=', 'sm.id')
            ->where('sm.status', 'pending')
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
