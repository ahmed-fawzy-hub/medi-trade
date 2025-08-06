<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutUs extends Model
{
    use HasFactory;

    protected $fillable = [
    'title_en', 'title_ar',
    'home_description_en', 'home_description_ar',
    'about_description_en', 'about_description_ar',
    'mission_en', 'mission_ar',
    'vision_en', 'vision_ar',
    'investments_en', 'investments_ar',
    'why_medi_trade_en', 'why_medi_trade_ar', // ✅ أضفهم هنا
    'image',
    'en_alt_image', 'ar_alt_image'
];

}
