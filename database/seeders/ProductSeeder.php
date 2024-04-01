<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 50; $i++) {
            $name = "Butterbeer Glitter Enamel Pin".substr(str_shuffle("ABSCDEFGHIJKLMNOPQRTUVXZ"), 0, 10);
            $product = new Product;
            $product->name = $name;
            $product->slug = \Illuminate\Support\Str::slug($name);
            $product->short_description = "Conjure up a chilled glass full of the most refreshing treat in the Wizarding World with this new and exclusive Butterbeer Glitter Enamel Pin.";
            $product->description = "Conjure up a chilled glass full of the most refreshing treat in the Wizarding World with this new and exclusive Butterbeer Glitter Enamel Pin.

            Crafted in the shape of a frothy pub stein, this pin perfectly captures the essence of everyone's favourite magical beverage. Adorned with the classic MinaLima Butterbeer logo, the pin showcases a gold rimmed glass filled to the top with sparkling, delicious Butterbeer, crowned with a delightful layer of foam!
            
            Limited in quantity and overflowing with charm, this Butterbeer pin is a must-have for any Potterhead. Quench your thirst for magical memorabilia and grab yours today!
            
            ";
            $product->price = 100.00;
            $product->featured = $i % 2 == 0 ? 1 : 0;
            $product->save();

            ProductImage::create([
                'product_id' => $product->id,
                'image' => $faker->imageUrl()
            ]);
        }
    }
}
