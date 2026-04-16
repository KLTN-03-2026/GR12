<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'is_active'];

    public function products()
    {
        // Một danh mục có nhiều sản phẩm
        return $this->hasMany(Product::class);
    }
}
