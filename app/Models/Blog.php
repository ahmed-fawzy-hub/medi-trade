<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Blog extends Model
{
    protected $fillable = [
    'title_en', 'title_ar',

    'short_description_en', 'short_description_ar',
    'full_description_en', 'full_description_ar',

    'en_meta_title', 'en_meta_description', 'ar_meta_title', 'ar_meta_description',

    'external_image', 'external_image_alt_en', 'external_image_alt_ar',
    'internal_image', 'internal_image_alt_en', 'internal_image_alt_ar',

        'header_image', // ✅ تمت إضافته هنا
    'header_image_alt_en', 'header_image_alt_ar',
    'slug_en', 'slug_ar', // ✅ التعديل هنا
    'is_active',
];


    protected static function booted()
{
    // static::creating(function ($blog) {
    //     // Generate slug_en
    //     $slugEn = Str::slug($blog->title_en);
    //     $originalSlugEn = $slugEn;
    //     $i = 1;
    //     while (Blog::where('slug_en', $slugEn)->exists()) {
    //         $slugEn = $originalSlugEn . '-' . $i++;
    //     }
    //     $blog->slug_en = $slugEn;

    //     // Set slug_ar as is
    //     $blog->slug_ar = $blog->title_ar;
    // });

    // static::updating(function ($blog) {
    //     // Generate slug_en
    //     $slugEn = Str::slug($blog->title_en);
    //     $originalSlugEn = $slugEn;
    //     $i = 1;
    //     while (
    //         Blog::where('slug_en', $slugEn)
    //             ->where('id', '!=', $blog->id)
    //             ->exists()
    //     ) {
    //         $slugEn = $originalSlugEn . '-' . $i++;
    //     }
    //     $blog->slug_en = $slugEn;

    //     // Update slug_ar as is
    //     $blog->slug_ar = $blog->title_ar;
    // });
}


    public function getHumanDateAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}
