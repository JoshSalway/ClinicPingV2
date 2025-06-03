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

    protected $casts = [
        'appointment_at' => 'datetime',
        'last_sent_at' => 'datetime',
    ];

    public function smsMessages()
    {
        return $this->hasMany(SmsMessage::class);
    }

    public function latestSmsMessage()
    {
        return $this->hasOne(SmsMessage::class)->latestOfMany('sent_at');
    }
}
