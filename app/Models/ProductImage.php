<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    // THÊM ĐOẠN NÀY VÀO ĐỂ CHO PHÉP LƯU DỮ LIỆU
    protected $fillable = [
        'product_id',
        'image_path',
    ];

    // Thiết lập quan hệ ngược lại với Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}