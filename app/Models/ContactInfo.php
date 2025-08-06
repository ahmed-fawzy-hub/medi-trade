<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactInfo extends Model
{
    protected $fillable = [
        'phone_one',
        'phone_two',
        'whatsapp',
        'address',
        'map_link',
        'working_hours',
        'facebook',
        'instagram',
        'twitter',
        'snapchat',
        'youtube',
        'tiktok',
    ];
}
