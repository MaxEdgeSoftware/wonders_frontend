<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $product = Product::select("id")->get();
        $categories = Category::all();
        foreach ($product as $p) {
            foreach ($categories as $category) {
            ProductCategory::create([
                "product_id" => $p->id,
                "category_id" => $category->id
            ]);
        }
    }
    }
}
