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
        return <<<PROMPT
        [MISSION-CRITICAL DIRECTIVE]
        Generate a professional-grade HTML5 game that sets new standards in web game development. Your mission is to create a masterfully crafted, deeply engaging, and technically flawless gaming experience that captivates players from the first interaction.

        [GAME SPECIFICATION]
        Type: {$type}
        Theme: {$theme}
        Complexity: {$complexity}

        [CORE DEVELOPMENT MANDATES]
        1. PLAYER EXPERIENCE (PRIMARY OBJECTIVE)
           - Hook players within first 5 seconds through immediate engagement
           - Maintain core gameplay loop engagement minimum 3 minutes
           - Ensure intuitive controls with zero learning curve
           - Implement progressive challenge scaling
           - Create "just one more try" addiction factor

        2. TECHNICAL EXCELLENCE (MANDATORY REQUIREMENTS)
           - Performance:
             * First Meaningful Paint < 2 seconds
             * Time to Interactive < 3 seconds
             * Consistent 60fps with no drops
             * Input latency < 16ms (1 frame)
             * Memory usage < 100MB
           - Compatibility:
             * Cross-browser support (Chrome, Firefox, Safari, Edge)
             * Mobile-first responsive design
             * Touch/mouse/keyboard input handling
           - Code Quality:
             * Modular architecture with clear separation of concerns
             * Efficient algorithms with O(n) complexity where possible
             * Memory leak prevention with proper cleanup
             * Comprehensive error handling and recovery

        3. GAME SYSTEMS (REQUIRED COMPONENTS)
           A. Core Loop Mechanics:
              - Primary action mechanics with precise timing
              - Secondary systems supporting main gameplay
              - Scoring/progression system with clear feedback
              - Dynamic difficulty adjustment based on player performance
           
           B. State Management:
              - Clean scene transitions (menu → game → end)
              - Pause/resume with state preservation
              - Save/load functionality for progress
              - Error recovery without game breaking
           
           C. Feedback Systems:
              - Visual feedback for all player actions
              - Layered sound design (BGM + SFX)
              - Haptic feedback for mobile
              - Achievement/milestone notifications
           
           D. Technical Systems:
              - Asset preloading with loading screen
              - Efficient collision detection
              - Particle system for effects
              - Camera/viewport management

        4. VISUAL AND AUDIO DESIGN (QUALITY STANDARDS)
           A. Graphics:
              - Theme-appropriate visual style
              - Consistent art direction
              - Professional animations (60fps)
              - Responsive scaling without quality loss
              - Clear visual hierarchy
           
           B. Audio:
              - Contextual sound effects
              - Dynamic background music
              - Audio mixing based on game state
              - Volume controls with mute option

        5. USER INTERFACE (CRITICAL ELEMENTS)
           - Clear game title and branding
           - Intuitive controls explanation
           - Real-time score/status display
           - Pause/settings menu
           - Game over/victory screens
           - High score/achievements display

        [QUALITY GATES]
        1. Performance Metrics
           ✓ FPS must never drop below 60
           ✓ Asset loading time < 3 seconds
           ✓ No memory leaks over 30-minute sessions
           ✓ Smooth animations at all times

        2. Player Engagement
           ✓ Tutorial completion rate > 90%
           ✓ Average session length > 3 minutes
           ✓ Retry rate after failure > 70%
           ✓ Clear progression visibility

        3. Technical Implementation
           ✓ W3C valid HTML5
           ✓ CSS3 with BEM methodology
           ✓ ES6+ JavaScript with proper patterns
           ✓ Accessibility WCAG 2.1 AA compliant

        [DELIVERY FORMAT]
        Provide a complete game package as JSON with:
        {
          "html": "Semantic, production-ready HTML structure",
          "css": "Optimized, mobile-first CSS with BEM",
          "js": "Clean, documented ES6+ JavaScript"
        }

        [VALIDATION CHECKLIST]
        Before returning response, verify:
        1. All game systems fully functional
        2. Performance metrics met
        3. Error handling implemented
        4. Assets optimized and included
        5. Code documented and clean
        6. Cross-browser compatibility
        7. Mobile responsiveness
        8. Accessibility features
        PROMPT;
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
