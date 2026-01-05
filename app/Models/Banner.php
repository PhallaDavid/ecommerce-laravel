<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'images', 'link', 'is_active'];

    protected $casts = [
        'images' => 'array',
        'is_active' => 'boolean',
    ];

    public function getImagesAttribute($value)
    {
        $images = json_decode($value, true) ?? [];
        return array_map(function ($image) {
            if (filter_var($image, FILTER_VALIDATE_URL)) {
                return $image;
            }
            return asset(ltrim($image, '/'));
        }, $images);
    }
}
