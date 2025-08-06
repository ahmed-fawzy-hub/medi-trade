<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Service extends Model
{
    protected $fillable = [
        'title_en', 'title_ar',
        'short_description_en', 'short_description_ar',
        'full_description_en', 'full_description_ar',
        'en_meta_title', 'en_meta_description', 'ar_meta_title', 'ar_meta_description',
        'slug_en', 'slug_ar',
        'main_image', 'header_image',
        'main_image_alt_en', 'main_image_alt_ar',
        'header_image_alt_en', 'header_image_alt_ar',
        'supplies_image', 'supplies_image_alt_en', 'supplies_image_alt_ar',
        'supplies_text_en', 'supplies_text_ar',
        'is_active',
    ];

protected $casts = [
    'is_active' => 'integer',
];

    protected static function booted()
    {
        static::creating(function ($service) {
            $service->slug_en = Str::slug($service->title_en); // إنجليزي: توليد slug
            $service->slug_ar = $service->title_ar;            // عربي: بدون تحويل
        });

        
    }
    
}
