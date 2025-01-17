<?php

namespace App\Services;

use OpenAI\Client;
use Exception;
use Illuminate\Support\Facades\Session;

class GameGeneratorService
{
    protected $client;
    protected $conversationKey = 'game_generation_conversation';
    protected $currentPhase = 1;

    public function __construct()
    {
        $this->client = \OpenAI::factory()
            ->withApiKey(env('DEEPSEEK_API_KEY'))
            ->withBaseUri('https://api.deepseek.com')
            ->make();
    }

    public function generateGamePhase($type, $theme, $complexity, $feedback = null)
    {
        try {
            $messages = Session::get($this->conversationKey, []);
            
            if (empty($messages)) {
                $messages = [
                    [
                        'role' => 'system',
                        'content' => 'You are a game development assistant that generates HTML5 games in phases.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $this->createPhasePrompt($type, $theme, $complexity, 1)
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
            
            $this->currentPhase++;
            
            return [
                'phase' => $this->currentPhase - 1,
                'content' => $parsed,
                'next_prompt' => $this->getNextPhasePrompt($type, $theme, $complexity)
            ];
            
        } catch (Exception $e) {
            throw new Exception('AI game generation failed: ' . $e->getMessage());
        }
    }

    private function createPhasePrompt($type, $theme, $complexity, $phase)
    {
        $phases = [
            1 => "Generate the core game concept and mechanics. Focus on:",
            2 => "Develop the visual style and UI elements. Include:",
            3 => "Implement the core gameplay loop. Ensure:",
            4 => "Add additional features and polish. Consider:",
            5 => "Final review and optimizations. Verify:"
        ];

        return <<<PROMPT
        [PHASE {$phase}: {$phases[$phase]}]
        Game Type: {$type}
        Theme: {$theme}
        Complexity: {$complexity}

        Provide output as JSON with:
        {
          "description": "Detailed description of this phase",
          "assets": "List of required assets",
          "tasks": "Development tasks for this phase"
        }
        PROMPT;
    }

    private function getNextPhasePrompt($type, $theme, $complexity)
    {
        if ($this->currentPhase > 5) {
            return null;
        }
        return $this->createPhasePrompt($type, $theme, $complexity, $this->currentPhase);
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

        return $data;
    }

    public function resetConversation()
    {
        Session::forget($this->conversationKey);
        $this->currentPhase = 1;
    }
}
