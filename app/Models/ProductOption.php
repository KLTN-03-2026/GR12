<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductOption extends Model
{
    use HasFactory;

    // THÊM ĐOẠN NÀY VÀO ĐỂ CHO PHÉP LƯU DỮ LIỆU
    protected $fillable = [
        'product_id',
        'option_name',
        'option_value',
        'additional_price',
    ];

    // Thiết lập quan hệ ngược lại với Product (nên có)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
