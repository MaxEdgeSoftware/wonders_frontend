<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            "Clothing", "Collectables", "Homeware", "Kids", "Souvenirs", "Sweets and Treats", "Trunks",
        ];
        foreach ($categories as $category) {
            if(Category::where("category", $category)->first()){

            }else{
                Category::create([
                    "category" => $category,
                    "image" => "https://via.placeholder.com/640x480.png/00ddbb?text=a"
                ]);
            }
        }
        //
    }
}
