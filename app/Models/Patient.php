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
        'user_id',
    ];

    protected $casts = [
        'appointment_at' => 'datetime',
        'last_sent_at' => 'datetime',
    ];

    public function smsMessages()
    {
        return $this->belongsToMany(SmsMessage::class, 'patient_sms_message');
    }

    public function latestSmsMessage()
    {
        return $this->belongsToMany(SmsMessage::class, 'patient_sms_message')
            ->latest('sent_at')
            ->first();
    }

    public function smsMessagesMany()
    {
        return $this->belongsToMany(SmsMessage::class, 'patient_sms_message');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class)->withDefault();
    }
}
