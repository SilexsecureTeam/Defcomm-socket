<?php

namespace Database\Seeders;

use App\Models\LanguageCode;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LanguageCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [
            // Speech-to-Text (source_language)
            [
                'language' => 'English (US)',
                'code'     => 'en-US',
                'short'    => 'en'
            ],
            [
                'language' => 'English (UK)',
                'code'     => 'en-GB',
                'short'    => 'en'
            ],
            [
                'language' => 'French (France)',
                'code'     => 'fr-FR',
                'short'    => 'fr'
            ],
            [
                'language' => 'Spanish (Spain)',
                'code'     => 'es-ES',
                'short'    => 'es'
            ],
            [
                'language' => 'Spanish (Mexico)',
                'code'     => 'es-MX',
                'short'    => 'es'
            ],
            [
                'language' => 'Portuguese (Brazil)',
                'code'     => 'pt-BR',
                'short'    => 'pt'
            ],
            [
                'language' => 'Portuguese (Portugal)',
                'code'     => 'pt-PT',
                'short'    => 'pt'
            ],
            [
                'language' => 'German',
                'code'     => 'de-DE',
                'short'    => 'de'
            ],
            [
                'language' => 'Italian',
                'code'     => 'it-IT',
                'short'    => 'it'
            ],
            [
                'language' => 'Arabic (Egypt)',
                'code'     => 'ar-EG',
                'short'    => 'ar'
            ],
            [
                'language' => 'Arabic (Saudi)',
                'code'     => 'ar-SA',
                'short'    => 'ar'
            ],
            [
                'language' => 'Hindi (India)',
                'code'     => 'hi-IN',
                'short'    => 'hi'
            ],
            [
                'language' => 'Yoruba (Nigeria)',
                'code'     => 'yo-NG',
                'short'    => 'yo'
            ],
            [
                'language' => 'Igbo (Nigeria)',
                'code'     => 'ig-NG',
                'short'    => 'ig'
            ],
            [
                'language' => 'Hausa (Nigeria)',
                'code'     => 'ha-NG',
                'short'    => 'ha'
            ],
            [
                'language' => 'Chinese (Simplified, China)',
                'code'     => 'zh-CN',
                'short'    => 'zh'
            ],
            [
                'language' => 'Japanese',
                'code'     => 'ja-JP',
                'short'    => 'ja'
            ],
            [
                'language' => 'Korean',
                'code'     => 'ko-KR',
                'short'    => 'ko'
            ],

            // Text-to-Speech (target_language)
            [
                'language' => 'English',
                'code'     => 'en',
                'short'    => 'en-US'
            ],
            [
                'language' => 'French',
                'code'     => 'fr',
                'short'    => 'fr-FR'
            ],
            [
                'language' => 'Spanish',
                'code'     => 'es',
                'short'    => 'es-ES'
            ],
            [
                'language' => 'Portuguese',
                'code'     => 'pt',
                'short'    => 'pt-BR'
            ],
            [
                'language' => 'German',
                'code'     => 'de',
                'short'    => 'de-DE'
            ],
            [
                'language' => 'Italian',
                'code'     => 'it',
                'short'    => 'it-IT'
            ],
            [
                'language' => 'Yoruba',
                'code'     => 'yo',
                'short'    => 'yo-NG'
            ],
            [
                'language' => 'Hausa',
                'code'     => 'ha',
                'short'    => 'ha-NG'
            ],
            [
                'language' => 'Igbo',
                'code'     => 'ig',
                'short'    => 'ig-NG'
            ],
            [
                'language' => 'Hindi',
                'code'     => 'hi',
                'short'    => 'hi-IN'
            ],
        ];


        foreach ($records as $dt) {
            LanguageCode::updateOrCreate([
                'code' => $dt['code'],
            ], [
                'language' => $dt['language'],
                'short' => $dt['short'],
            ]);
        }
    }
}
