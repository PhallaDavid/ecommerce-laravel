<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->name);
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->name, $product->id);
            }
        });
    }

    protected static function generateUniqueSlug($name, $excludeId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        $query = static::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;

            $query = static::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'sale_price',
        'promotion_price',
        'discount_percent',
        'stock',
        'category_id',
        'images',
        'sku',
        'barcode',
        'featured',
        'is_active',
        'weight',
        'length',
        'width',
        'height',
        'rating',
        'sold_count',
        'promotion_start',
        'promotion_end',
        'sizes',
        'colors' // <-- added
    ];

    protected $casts = [
        'images' => 'array',
        'sizes' => 'array',   // <-- added
        'colors' => 'array',  // <-- added
        'featured' => 'boolean',
        'is_active' => 'boolean',
        'promotion_start' => 'datetime',
        'promotion_end' => 'datetime',
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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
