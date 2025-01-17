<?php

namespace App\Services;

use OpenAI\Client;
use Exception;

class GameGeneratorService
{
    protected $client;

    public function __construct()
    {
        $this->client = \OpenAI::factory()
            ->withApiKey(env('DEEPSEEK_API_KEY'))
            ->withBaseUri('https://api.deepseek.com')
            ->make();
    }

    public function generateGameCode($type, $theme, $complexity)
    {
        try {
            $prompt = $this->createPrompt($type, $theme, $complexity);
            
            $response = $this->client->chat()->create([
                'model' => 'deepseek-chat',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a game development assistant that generates complete HTML5 games.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 2000
            ]);

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
                'js' => $parsed['js']
            ];
            
        } catch (Exception $e) {
            throw new Exception('AI game generation failed: ' . $e->getMessage());
        }
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