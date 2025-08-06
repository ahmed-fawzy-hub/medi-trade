<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name_en', 'name_ar', 'is_active'];

    public function partners()
    {
        return $this->hasMany(Partner::class); // Laravel هيفهم تلقائيًا إنها مربوطة بـ category_id
    }
}
