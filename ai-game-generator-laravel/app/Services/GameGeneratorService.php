<?php

namespace App\Services;

use OpenAI\Client;
use Exception;
use Illuminate\Support\Facades\Session;

class GameGeneratorService
{
    protected $client;
    protected $conversationKey = 'game_generation_conversation';

    public function __construct()
    {
        $this->client = \OpenAI::factory()
            ->withApiKey(env('DEEPSEEK_API_KEY'))
            ->withBaseUri('https://api.deepseek.com')
            ->make();
    }

    public function generateGameCode($type, $theme, $complexity, $iteration = 1)
    {
        try {
            // Get or initialize conversation history
            $messages = Session::get($this->conversationKey, []);
            
            // First turn - initialize conversation
            if ($iteration === 1) {
                $messages = [
                    [
                        'role' => 'system',
                        'content' => 'You are a game development assistant that generates complete HTML5 games.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $this->createPrompt($type, $theme, $complexity)
                    ]
                ];
            } else {
                // Subsequent turns - add user feedback
                $messages[] = [
                    'role' => 'user',
                    'content' => "Please improve the game based on the following feedback: {$theme}"
                ];
            }

            // Generate response
            $response = $this->client->chat()->create([
                'model' => 'deepseek-chat',
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 2000
            ]);

            // Add assistant response to conversation history
            $messages[] = $response->choices[0]->message;
            Session::put($this->conversationKey, $messages);

            // Parse and return game files
            $content = $response->choices[0]->message->content;
            $parsed = $this->parseResponse($content);
            
            // Modify HTML to properly link CSS and JS files
            $html = str_replace(
                ['</head>', '</body>'],
                [
                    '<link rel="stylesheet" href="style.css"></head>',
                    '<script src="script.js"></script></body>'
                ],
                $parsed['html']
            );

            return [
                'html' => $html,
                'css' => $parsed['css'],
                'js' => $parsed['js'],
                'iteration' => $iteration
            ];
            
        } catch (Exception $e) {
            throw new Exception('AI game generation failed: ' . $e->getMessage());
        }
    }

    public function resetConversation()
    {
        Session::forget($this->conversationKey);
    }

    private function createPrompt($type, $theme, $complexity)
    {
        return "Generate a complete HTML5 game with the following specifications:
        - Game Type: {$type}
        - Theme: {$theme}
        - Complexity: {$complexity}
        
        Provide the response in JSON format with three keys:
        - html: The complete HTML code
        - css: The complete CSS code
        - js: The complete JavaScript code
        
        The game should be self-contained and work in a single HTML file when combined.";
    }

    private function parseResponse($content)
    {
        $pattern = '/```json(.*?)```/s';
        preg_match($pattern, $content, $matches);
        
        if (empty($matches)) {
            throw new Exception('Invalid AI response format');
        }

        $data = json_decode($matches[1], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to parse AI response: ' . json_last_error_msg());
        }

        return [
            'html' => $data['html'] ?? '',
            'css' => $data['css'] ?? '',
            'js' => $data['js'] ?? ''
        ];
    }
}