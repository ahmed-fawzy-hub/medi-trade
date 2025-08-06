<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoTag extends Model
{
protected $fillable = [
        'page_name',
        'en_meta_title',
        'en_meta_description',
        'ar_meta_title',
        'ar_meta_description',
        
    ];
}
