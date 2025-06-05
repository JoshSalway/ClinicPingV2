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
        'user_id',
    ];

    protected $casts = [
        'appointment_at' => 'datetime',
    ];

    public function smsMessages()
    {
        return $this->belongsToMany(SmsMessage::class)
            ->withTimestamps()
            ->select(['sms_messages.id', 'sms_messages.content', 'sms_messages.sent_at', 'sms_messages.completed_at', 'sms_messages.failed_at']);
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

    public function formSubmissions()
    {
        return $this->hasMany(FormSubmission::class);
    }
}
