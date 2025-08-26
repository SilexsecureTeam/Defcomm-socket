<?php

namespace App\Http\Services;

use Google\ApiCore\ApiException;
use Google\Cloud\Speech\V2\Client\SpeechClient;
use Google\Cloud\Speech\V2\RecognitionConfig;
use Google\Cloud\Speech\V2\RecognitionFeatures;
use Google\Cloud\Speech\V2\RecognizeRequest;
use Google\Cloud\Speech\V2\RecognitionAudio; //
use Google\Cloud\Speech\V2\RecognitionConfig\AudioEncoding;
use Google\Cloud\Translate\V3\Client\TranslationServiceClient;
use Google\Cloud\Translate\V3\TranslateTextRequest;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;//
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding as TtsAudioEncoding;

class GoogleAiService
{
    private string $projectId;
    private string $location;
    private array $credentials;

    public function __construct()
    {
        $this->projectId = config('services.google.project_id');
        $this->location  = config('services.google.location', 'global');
        $this->credentials = [
            'credentials' => config('services.google.credentials'),
        ];
    }

    /**
     * 🎙 Speech-to-Text
     */
    public function speechToText(string $audioPath, string $languageCode = 'en-US'): ?string
    {
        $client = new SpeechClient($this->credentials);

        try {
            $config = (new RecognitionConfig())
                ->setAutoDecodingConfig(new \Google\Cloud\Speech\V2\AutoDetectDecodingConfig())
                ->setLanguageCodes([$languageCode])
                ->setFeatures((new RecognitionFeatures())->setEnableAutomaticPunctuation(true));

            $audio = (new RecognitionAudio())->setContent(file_get_contents($audioPath));

            $request = (new RecognizeRequest())
                ->setRecognizer("projects/{$this->projectId}/locations/{$this->location}/recognizers/_")
                ->setConfig($config)
                ->setAudio($audio);

            $response = $client->recognize($request);

            $transcript = '';
            foreach ($response->getResults() as $result) {
                $transcript .= $result->getAlternatives()[0]->getTranscript() . ' ';
            }

            return trim($transcript);
        } catch (ApiException $e) {
            logger()->error('Speech-to-Text failed: ' . $e->getMessage());
            return null;
        } finally {
            $client->close();
        }
    }

    /**
     * 🌍 Translate Text
     */
    public function translateText(string $text, string $targetLanguageCode = 'fr'): ?string
    {
        $client = new TranslationServiceClient($this->credentials);

        try {
            $request = (new TranslateTextRequest())
                ->setParent("projects/{$this->projectId}/locations/{$this->location}")
                ->setContents([$text])
                ->setTargetLanguageCode($targetLanguageCode);

            $response = $client->translateText($request);

            return $response->getTranslations()[0]->getTranslatedText();
        } catch (ApiException $e) {
            logger()->error('Translation failed: ' . $e->getMessage());
            return null;
        } finally {
            $client->close();
        }
    }

    /**
     * 🔊 Text-to-Speech
     */
    public function textToSpeech(string $text, string $languageCode = 'en-US', string $voiceName = 'en-US-Wavenet-D'): ?string
    {
        $client = new TextToSpeechClient($this->credentials);

        try {
            $inputText = (new SynthesisInput())->setText($text);

            $voice = (new VoiceSelectionParams())
                ->setLanguageCode($languageCode)
                ->setName($voiceName);

            $audioConfig = (new AudioConfig())
                ->setAudioEncoding(TtsAudioEncoding::MP3);

            $response = $client->synthesizeSpeech($inputText, $voice, $audioConfig);

            $outputPath = storage_path('app/public/tts_output_' . time() . '.mp3');
            file_put_contents($outputPath, $response->getAudioContent());

            return $outputPath;
        } catch (ApiException $e) {
            logger()->error('Text-to-Speech failed: ' . $e->getMessage());
            return null;
        } finally {
            $client->close();
        }
    }
}
