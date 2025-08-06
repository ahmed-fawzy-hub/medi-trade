<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'medias';

    protected $fillable = [
        'type',
        'file_path', // تم التعديل هنا
        'video_url',
        'alt_text',

        'is_active',
    ];
}
