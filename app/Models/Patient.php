<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Patient extends Model
{
    use HasFactory;
    //

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'appointment_at',
        'last_sent_at',
        'status',
    ];

    public function smsMessages()
    {
        return $this->belongsToMany(SmsMessage::class, 'patient_sms_message');
    }
}
