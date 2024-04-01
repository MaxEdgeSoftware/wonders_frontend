<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $guarded  = [];
    public function Categories(){
        return $this->hasMany(ProductCategory::class, "product_id", "id");
    }
    public function Gallery(){
        return $this->hasMany(ProductImage::class);
    }
}
