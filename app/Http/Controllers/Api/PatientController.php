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

        // Today's appointments filter (default true)
        $todayOnly = $request->input('today_only', 'true') === 'true';
        if ($todayOnly) {
            $query->whereDate('appointment_at', today());
        }

        // Status filter
        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        // Sorting and filtering for last_sent
        if ($request->input('sortBy') === 'last_sent') {
            $query->whereNotNull('last_sent_at');
            $query->orderBy('last_sent_at', 'desc');
        } else {
            // Sorting
            $sortBy = $request->input('sortBy', 'appointment_at');
            $sortDir = $request->input('sortDir', 'asc');
            $query->orderBy($sortBy, $sortDir);
        }

        // Search by name or phone (normalize phone for AU)
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $normalizedSearch = preg_replace('/\D+/', '', $search);
                $q->whereRaw("LOWER(first_name) like ?", ["%".strtolower($search)."%"])
                  ->orWhereRaw("LOWER(last_name) like ?", ["%".strtolower($search)."%"])
                  ->orWhereRaw("substr(REPLACE(REPLACE(REPLACE(phone, '+61', '0'), ' ', ''), '-', ''), -9) = ?", [substr($normalizedSearch, -9)]);
            });
        }

        $patients = $query->paginate(10);

        // Transform data to include appointment_date, appointment_time, and sms_messages
        $patients->getCollection()->transform(function ($patient) {
            $appointment_at = $patient->appointment_at;
            $date = $appointment_at ? date('Y-m-d', strtotime($appointment_at)) : null;
            $time = $appointment_at ? date('H:i', strtotime($appointment_at)) : null;
            $patient->appointment_date = $date;
            $patient->appointment_time = $time;
            // Add sms_messages (id, status, sent_at)
            $patient->sms_messages = $patient->smsMessages()->orderByDesc('sent_at')->get(['id', 'status', 'sent_at']);
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'phone'      => ['required', 'regex:/^\+[1-9]\d{7,14}$/'],
            'email'      => 'nullable|email',
        ]);
        $patient = Patient::create($validated);
        return response()->json($patient, 201);
    }
} 