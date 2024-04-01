<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            [
                "title" => "German",
                "country" => "Germany",
                "code" => "de",
            ],
            [
                "title" => "English",
                "country" => "english",
                "code" => "en",
            ],
            [
                "title" => "French",
                "country" => "french",
                "code" => "fr",
            ],
            [
                "title" => "Spanish",
                "country" => "spanish",
                "code" => "es",
            ]
        ];

        foreach ($languages as $language) {
            Language::create([
                "title" => $language["title"],
                "country" => $language["country"],
                "code" => $language["code"],
            ]);
        }
    }
}
