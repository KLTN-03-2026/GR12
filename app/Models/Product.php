<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'name', 'slug', 'description',
        'price', 'discount_price', 'image', 'stock_quantity', 'is_available', 'available_from', 'available_to', 'status', 'reject_reason'
    ];

    public function category()
    {
        // Một sản phẩm thuộc về một danh mục
        return $this->belongsTo(Category::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function options()
    {
        // Một sản phẩm có nhiều tùy chọn (size, topping)
        return $this->hasMany(ProductOption::class);
    }
    
    public function gallery()
    {
        return $this->hasMany(ProductImage::class);
    }

    // app/Models/Product.php
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeVisible($query)
    {
        return $query->where('status', 'approved')->where('is_available', true);
    }

    // Hàm tính số sao trung bình
    public function getAverageRatingAttribute()
    {
        return round($this->reviews()->avg('rating') ?: 5, 1);
    }
}
