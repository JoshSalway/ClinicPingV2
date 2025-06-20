<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsMessage extends Model
{
    /** @use HasFactory<\Database\Factories\SmsMessageFactory> */
    use HasFactory;

    protected $fillable = [
        'content',
        'sent_at',
        'completed_at',
        'failed_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'patient_sms_message');
    }
}
