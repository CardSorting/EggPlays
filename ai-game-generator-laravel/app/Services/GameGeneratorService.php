<?php

namespace App\Services;

use OpenAI\Client;
use Exception;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;

class GameGeneratorService
{
    protected $client;
    protected $conversationKey = 'game_generation_conversation';
    protected $maxRetries = 3;
    protected $retryDelay = 2; // seconds

    public function __construct()
    {
        $this->client = \OpenAI::factory()
            ->withApiKey(env('DEEPSEEK_API_KEY'))
            ->withBaseUri('https://api.deepseek.com')
            ->withHttpClient(new \GuzzleHttp\Client([
                'timeout' => 60, // 60 second timeout
                'connect_timeout' => 10
            ]))
            ->make();
    }

    public function generateGamePhase($type, $feedback = null)
    {
        $attempt = 0;
        
        while ($attempt < $this->maxRetries) {
            try {
                $messages = Session::get($this->conversationKey, []);
                
                if (empty($messages)) {
                    $messages = [
                        [
                            'role' => 'system',
                            'content' => 'You are a game development assistant that generates simple HTML5 games with start menu, gameplay loop, and game over screen.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $this->createGameLoopPrompt($type)
                        ]
                    ];
                } else if ($feedback) {
                    $messages[] = [
                        'role' => 'user',
                        'content' => $feedback
                    ];
                }

                $response = $this->client->chat()->create([
                    'model' => 'deepseek-chat',
                    'messages' => $messages,
                    'temperature' => 0.7,
                    'max_tokens' => 2000
                ]);

                $messages[] = $response->choices[0]->message;
                Session::put($this->conversationKey, $messages);

                $content = $response->choices[0]->message->content;
                $parsed = $this->parseResponse($content);
                
                return [
                    'content' => $parsed,
                    'html' => $parsed['html'] ?? '',
                    'css' => $parsed['css'] ?? '',
                    'js' => $parsed['js'] ?? ''
                ];
                
            } catch (Exception $e) {
                $attempt++;
                Log::warning('Game generation attempt failed', [
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                    'stack' => $e->getTraceAsString()
                ]);
                
                if ($attempt >= $this->maxRetries) {
                    Log::error('Game generation failed after maximum retries', [
                        'error' => $e->getMessage(),
                        'stack' => $e->getTraceAsString()
                    ]);
                    throw new Exception('AI game generation failed: ' . $e->getMessage());
                }
                
                Sleep::for($this->retryDelay)->seconds();
            }
        }
    }

    private function createGameLoopPrompt($type)
    {
        return <<<PROMPT
        Generate a simple HTML5 game with:
        1. Start menu with play button
        2. Core gameplay loop
        3. Game over screen with restart option
        
        Game Type: {$type}

        Provide output as JSON with:
        {
          "description": "Brief description of the game flow",
          "html": "HTML structure including all three screens",
          "css": "CSS styles for all components",
          "js": "JavaScript code implementing game flow between screens"
        }
        PROMPT;
    }

    private function parseResponse($content)
    {
        // First try parsing the content directly as JSON
        $data = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        }

        // If direct parsing fails, try extracting JSON from code blocks
        $pattern = '/```(?:json)?\s*(.*?)```/s';
        preg_match($pattern, $content, $matches);
        
        if (!empty($matches)) {
            $data = json_decode($matches[1], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $data;
            }
        }

        // If both attempts fail, log the content and throw an exception
        Log::error('Failed to parse AI response', [
            'content' => $content,
            'json_error' => json_last_error_msg()
        ]);
        
        throw new Exception('Invalid AI response format. Expected JSON or JSON code block.');
    }

    public function resetConversation()
    {
        Session::forget($this->conversationKey);
    }
}
