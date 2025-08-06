<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = [
        'name_en',
        'name_ar',
        'image',
        'en_alt_image',
        'ar_alt_image',
        'category_id',
        'is_active',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
