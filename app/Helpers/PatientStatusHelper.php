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
        // Completed: has at least one SMS with completed_at not null
        if ($patient->smsMessages()->whereNotNull('completed_at')->exists()) {
            return 'completed';
        }
        // Sent: has at least one SMS with sent_at not null and completed_at is null
        if ($patient->smsMessages()->whereNotNull('sent_at')->whereNull('completed_at')->where('status', 'sent')->exists()) {
            return 'sent';
        }
        // Failed: has at least one failed SMS
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