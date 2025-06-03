<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $query = Patient::query();

        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        $sortBy = $request->input('sortBy', 'name');
        $query->orderBy($sortBy);

        $patients = $query->paginate(10);

        // Transform data to include appointment_date and appointment_time
        $patients->getCollection()->transform(function ($patient) {
            $appointment_at = $patient->appointment_at;
            $date = $appointment_at ? date('Y-m-d', strtotime($appointment_at)) : null;
            $time = $appointment_at ? date('H:i', strtotime($appointment_at)) : null;
            $patient->appointment_date = $date;
            $patient->appointment_time = $time;
            return $patient;
        });

        return response()->json($patients);
    }

    public function show($id)
    {
        $patient = Patient::findOrFail($id);
        $appointment_at = $patient->appointment_at;
        $date = $appointment_at ? date('Y-m-d', strtotime($appointment_at)) : null;
        $time = $appointment_at ? date('H:i', strtotime($appointment_at)) : null;
        $smsMessages = $patient->smsMessages()->orderByDesc('sent_at')->get(['id', 'content', 'status', 'sent_at']);
        return response()->json([
            'id' => $patient->id,
            'first_name' => $patient->first_name,
            'last_name' => $patient->last_name,
            'phone' => $patient->phone,
            'email' => $patient->email,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'status' => $patient->status,
            'last_sent_at' => $patient->last_sent_at,
            'sms_messages' => $smsMessages,
        ]);
    }
} 