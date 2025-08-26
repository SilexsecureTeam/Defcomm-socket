<?php

namespace App\Http\Services;

use Google\Protobuf\FieldMask;
use Google\ApiCore\ApiException;
use Google\Cloud\Speech\V2\RecognizeRequest;
use Google\Cloud\Speech\V2\RecognitionConfig;
use Google\Cloud\Speech\V2\Client\SpeechClient;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\Translate\V3\TranslateTextRequest;
use Google\Cloud\Speech\V2\AutoDetectDecodingConfig;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Google\Cloud\TextToSpeech\V1\Client\TextToSpeechClient;
use Google\Cloud\Translate\V3\Client\TranslationServiceClient;
use Google\Cloud\TextToSpeech\V1\AudioConfig as TtsAudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding as TtsAudioEncoding;

class GoogleAiTransService
{
    private string $projectId;
    private string $location;
    private array $credentials;

    public function __construct()
    {
        $this->projectId = config('services.google.cloud_project');
        $this->location  = config('services.google.location');
        $this->credentials = [
            'credentials' => config('services.google.application_credential'),
        ];
    }

    /**
     * 🎤 Convert speech audio file to text (Speech-to-Text V2)
     */
    public function speechToText(string $filePath, string $sourceLanguage = 'en-US'): string
    {
        $client = new SpeechClient();

        $recognizer = $client->recognizerName($this->projectId, $this->location, '_'); // "_" = default recognizer

        $config = new RecognitionConfig([
            'auto_decoding_config' => new AutoDetectDecodingConfig(),
            'language_codes' => [$sourceLanguage],
        ]);

        $request = (new RecognizeRequest())
            ->setRecognizer($recognizer)
            ->setConfig($config)
            ->setConfigMask(new FieldMask()) // required
            ->setContent(file_get_contents($filePath));

        try {
            $response = $client->recognize($request);

            $transcript = '';
            foreach ($response->getResults() as $result) {
                if ($alt = $result->getAlternatives()[0] ?? null) {
                    $transcript .= $alt->getTranscript() . ' ';
                }
            }

            return trim($transcript);
        } catch (ApiException $e) {
            throw new \Exception("Speech-to-Text failed: " . $e->getMessage());
        } finally {
            $client->close();
        }
    }

    /**
     * 🌍 Translate text (Translate V3)
     */
    public function translateText(string $text, string $targetLanguage = 'fr', string $sourceLanguage = 'en'): string
    {
        $client = new TranslationServiceClient();

        $parent = $client->locationName($this->projectId, $this->location);

        $request = (new TranslateTextRequest())
            ->setParent($parent)
            ->setMimeType('text/plain')
            ->setTargetLanguageCode($targetLanguage)
            ->setSourceLanguageCode($sourceLanguage)
            ->setContents([$text]);

        try {
            $response = $client->translateText($request);
            $translations = $response->getTranslations();

            return count($translations) > 0 ? $translations[0]->getTranslatedText() : '';
        } catch (ApiException $e) {
            throw new \Exception("Translation failed: " . $e->getMessage());
        } finally {
            $client->close();
        }
    }

    /**
     * 🔊 Convert text to speech (Text-to-Speech V1)
     */
    public function textToSpeech(string $text, string $languageCode = 'en-US', string $voiceName = 'en-US-Wavenet-D'): string
    {
        $client = new TextToSpeechClient();

        $inputText = new SynthesisInput();
        $inputText->setText($text);

        $voice = new VoiceSelectionParams();
        $voice->setLanguageCode($languageCode);
        $voice->setName($voiceName);

        $audioConfig = new TtsAudioConfig();
        $audioConfig->setAudioEncoding(TtsAudioEncoding::MP3);

        try {
            $response = $client->synthesizeSpeech($inputText, $voice, $audioConfig);

            return $response->getAudioContent();
        } catch (ApiException $e) {
            throw new \Exception("Text-to-Speech failed: " . $e->getMessage());
        } finally {
            $client->close();
        }
    }
}


// Speech - to - Text(source_language)
// | Language                    | Code    |
// | --------------------------- | ------- |
// | English (US)                | `en-US` |
// | English (UK)                | `en-GB` |
// | French (France)             | `fr-FR` |
// | Spanish (Spain)             | `es-ES` |
// | Spanish (Mexico)            | `es-MX` |
// | Portuguese (Brazil)         | `pt-BR` |
// | Portuguese (Portugal)       | `pt-PT` |
// | German                      | `de-DE` |
// | Italian                     | `it-IT` |
// | Arabic (Egypt)              | `ar-EG` |
// | Arabic (Saudi)              | `ar-SA` |
// | Hindi (India)               | `hi-IN` |
// | Yoruba (Nigeria)            | `yo-NG` |
// | Igbo (Nigeria)              | `ig-NG` |
// | Hausa (Nigeria)             | `ha-NG` |
// | Chinese (Simplified, China) | `zh-CN` |
// | Japanese                    | `ja-JP` |
// | Korean                      | `ko-KR` |


// Text - to - Speech(target_language)
// | Language   | Translate API Code | TTS/Voice Code |
// | ---------- | ------------------ | -------------- |
// | English    | `en`               | `en-US`        |
// | French     | `fr`               | `fr-FR`        |
// | Spanish    | `es`               | `es-ES`        |
// | Portuguese | `pt`               | `pt-BR`        |
// | German     | `de`               | `de-DE`        |
// | Italian    | `it`               | `it-IT`        |
// | Yoruba     | `yo`               | `yo-NG`        |
// | Hausa      | `ha`               | `ha-NG`        |
// | Igbo       | `ig`               | `ig-NG`        |
// | Hindi      | `hi`               | `hi-IN`        |
