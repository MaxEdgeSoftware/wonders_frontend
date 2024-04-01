<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                "title" => "Euro - €",
                "sub" => "Euro",
                "code" => "euro",
                "symbol" => "€",
                "name" => "EUR",
            ],

            [
                "title" => "Pound - £",
                "sub" => "British pound sterling",
                "code" => "GBP",
                "symbol" => "£",
                "name" => "GBP",
            ],

            [
                "title" => "Dollar  - $",
                "sub" => "United States dollar",
                "code" => "dollar",
                "symbol" => "$",
                "name" => "USD",
            ],

            [
                "title" => "Rupee - ₹",
                "sub" => "Indian rupee",
                "code" => "rupee",
                "symbol" => "₹",
                "name" => "RUPEE",
            ],

            [
                "title" => "Won - ₩",
                "sub" => "South Korean won",
                "code" => "won",
                "symbol" => "₩",
                "name" => "WON",
            ],

            [
                "title" => "Yen - ¥",
                "sub" => "Japanese yen",
                "code" => "yen",
                "symbol" => "¥",
                "name" => "YEN",
            ],

            [
                "title" => "Yuan - ¥",
                "sub" => "Chinese Yuan",
                "code" => "yuan",
                "symbol" => "¥",
                "name" => "YUAN",
            ]
        ];
        foreach ($currencies as $currency) {
            Currency::create([
                "title" => $currency["title"],
                "sub" => $currency["sub"],
                "code" => $currency["code"],
                "symbol" => $currency["symbol"],
                "name" => $currency["name"],
            ]);
        }
    }
}
