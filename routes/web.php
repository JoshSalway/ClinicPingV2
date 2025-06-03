<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use App\Models\SmsMessage;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $totalPatients = Patient::count();
        $formsSentToday = SmsMessage::whereDate('sent_at', today())->count();
        $todaysAppointments = Patient::whereDate('appointment_at', today())->count();

        // Subquery: latest sent_at per patient
        $latestSmsSub = DB::table('patient_sms_message as psm')
            ->select('psm.patient_id', DB::raw('MAX(sm.sent_at) as latest_sent_at'))
            ->join('sms_messages as sm', 'psm.sms_message_id', '=', 'sm.id')
            ->groupBy('psm.patient_id');

        // Join patients to their latest sms message and count those with status 'pending'
        $pendingForms = DB::table('patients as p')
            ->joinSub($latestSmsSub, 'latest_sms', function ($join) {
                $join->on('p.id', '=', 'latest_sms.patient_id');
            })
            ->join('patient_sms_message as psm', 'p.id', '=', 'psm.patient_id')
            ->join('sms_messages as sm', function ($join) {
                $join->on('psm.sms_message_id', '=', 'sm.id')
                     ->on('sm.sent_at', '=', 'latest_sms.latest_sent_at');
            })
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

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
