<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use App\Models\Patient;
use App\Models\SmsMessage;
use Twilio\Rest\Client;

class SmsController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'message' => 'required|string',
        ]);

        $patient = Patient::findOrFail($request->patient_id);
        $mode = config('services.sms_mode', 'demo');
        $status = 'sent';
        $error = null;

        if ($mode === 'twilio') {
            try {
                $twilio = new Client(config('services.twilio.sid'), config('services.twilio.token'));
                $twilio->messages->create($patient->phone, [
                    'from' => config('services.twilio.from'),
                    'body' => $request->message,
                ]);
            } catch (\Exception $e) {
                $status = 'failed';
                $error = $e->getMessage();
                Log::error('Twilio SMS failed: ' . $error);
            }
        } else {
            // Demo mode: simulate sending
            Log::info('Simulated SMS to ' . $patient->phone . ': ' . $request->message);
        }

        // Record in DB regardless of mode
        $sms = SmsMessage::create([
            'content' => $request->message,
            'status' => $status,
            'sent_at' => now(),
        ]);
        $sms->patients()->syncWithoutDetaching([$patient->id]);
        $patient->update([
            'status' => $status,
            'last_sent_at' => now(),
        ]);

        if ($status === 'failed') {
            return response()->json(['success' => false, 'error' => $error], 500);
        }
        return response()->json(['success' => true]);
    }
} 