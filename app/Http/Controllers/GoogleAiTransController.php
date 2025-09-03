<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\GoogleAiTransService;

class GoogleAiTransController extends Controller
{
    protected GoogleAiTransService $googleAi;

    public function __construct(GoogleAiTransService $googleAi)
    {
        $this->googleAi = $googleAi;
    }

    /**
     * 🎤 Speech-to-Text
     */
    public function speechToText(Request $request)
    {
        // $request->validate([
        //     'audio' => 'required|file|mimes:mp3,wav,m4a',
        //     'language' => 'required|string'
        // ]);

        $audioPath = $request->file('audio')->getRealPath();
        $language = $request->input('language', 'en-US');

        try {
            $transcript = $this->googleAi->speechToText($audioPath, $language);

            return response()->json([
                'transcript' => $transcript,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 🌍 Translate
     */
    public function translateText(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
            'target_lang' => 'required|string',
            'source_lang' => 'nullable|string'
        ]);

        $text = $request->input('text');
        $target = $request->input('target_lang');
        $source = $request->input('source_lang', 'en');

        try {
            $translation = $this->googleAi->translateText($text, $target, $source);

            return response()->json([
                'original_text'   => $text,
                'translated_text' => $translation,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 🔊 Text-to-Speech
     */
    public function textToSpeech(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
            'language' => 'required|string'
        ]);

        $text = $request->input('text');
        $language = $request->input('language');

        try {
            $audioContent = $this->googleAi->textToSpeech($text, $language);

            // Save to file
            $outputFile = public_path('storage/translate/tts_' . time() . '.mp3');
            file_put_contents($outputFile, $audioContent);

            return response()->json([
                'text' => $text,
                'audio_file_url' => asset('storage/translate/' . basename($outputFile)),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 🎤 Upload audio → transcribe → translate → synthesize speech
     */
    public function speechToSpeech(Request $request)
    {
        // $request->validate([
        //     'audio' => 'required|file|mimes:mp3,wav,m4a',
        //     'source_lang' => 'required|string',
        //     'target_lang' => 'required|string',
        // ]);

        $audioPath = $request->type == 'file' ? $request->file('audio')->getRealPath() : $request->audio;
        // return dd($audioPath);
        $sourceLang = $request->input('source_lang', 'en-US');
        $targetLang = $request->input('target_lang', 'fr');

        try {
            // 1️⃣ Transcribe speech to text
            $transcript = $this->googleAi->speechToText($audioPath, $sourceLang);

            // 2️⃣ Translate text
            $translation = $this->googleAi->translateText($transcript, $targetLang, substr($sourceLang, 0, 2));

            // 3️⃣ Convert translation back to speech
            $audioContent = $this->googleAi->textToSpeech($translation, $targetLang);

            // Save MP3 file
            $outputFile = public_path('storage/translate/translated_audio_' . time() . '.mp3');
            file_put_contents($outputFile, $audioContent);

            return response()->json([
                'original_text'   => $transcript,
                'translated_text' => $translation,
                'audio_file_url'  => asset('storage/translate/' . basename($outputFile)),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
