<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivacyPolicy extends Model
{
    protected $table = 'privacy_policy'; 
protected $fillable = [
        'en_title',
        'en_description',
        'ar_title',
        'ar_description',
    ];}
