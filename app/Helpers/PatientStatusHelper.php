<?php

namespace App\Helpers;

use App\Models\Patient;

class PatientStatusHelper
{
    /**
     * Determine the status of a patient based on their SMS messages.
     * In the future, this can also consider form completion, etc.
     */
    public static function getStatus(Patient $patient): string
    {
        if ($patient->smsMessages()->where('status', 'completed')->exists()) {
            return 'completed';
        }
        if ($patient->smsMessages()->where('status', 'sent')->exists()) {
            return 'sent';
        }
        if ($patient->smsMessages()->where('status', 'failed')->exists()) {
            return 'failed';
        }
        return 'pending';
    }

    /**
     * Get a human-readable label for a status.
     */
    public static function getLabel(string $status): string
    {
        return match($status) {
            'pending' => 'Pending',
            'sent' => 'Sent',
            'completed' => 'Completed',
            'failed' => 'Failed',
            default => ucfirst($status),
        };
    }
} 