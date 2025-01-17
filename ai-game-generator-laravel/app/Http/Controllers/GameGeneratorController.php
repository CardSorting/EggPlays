<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\GameGeneratorService;
use Exception;
use Illuminate\Support\Facades\Log;

class GameGeneratorController extends Controller
{
    protected $gameService;

    public function __construct(GameGeneratorService $gameService)
    {
        $this->gameService = $gameService;
    }

    // Show the game generator form
    public function showGenerator()
    {
        return view('game-generator');
    }

    // Handle game generation phase
    public function generateGamePhase(Request $request)
    {
        $request->validate([
            'game_type' => 'required|string',
            'feedback' => 'sometimes|array'
        ]);

        try {
            $gameId = $request->game_id ?? Str::uuid();
            
            Log::debug('Generating game', [
                'game_id' => $gameId,
                'type' => $request->game_type
            ]);

            // Format feedback for AI
            $formattedFeedback = $this->formatFeedback($request->feedback);

            // Generate game content
            $gameContent = $this->gameService->generateGamePhase(
                $request->game_type,
                $formattedFeedback
            );

            // Store game files
            $this->storeGameFiles($gameId, $gameContent);

            Log::debug('Game stored', [
                'game_id' => $gameId
            ]);

            return redirect()->route('game.show', ['id' => $gameId])
                ->with('success', 'Game generated successfully!')
                ->with('gameUrl', asset("storage/games/{$gameId}/index.html"));

        } catch (Exception $e) {
            Log::error('Game generation failed', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return back()->withInput()
                ->withErrors(['error' => 'Game generation failed: ' . $e->getMessage()]);
        }
    }

    // Show generated game
    public function showGame($id)
    {
        $gamePath = "games/{$id}";
        
        if (!Storage::disk('public')->exists("{$gamePath}/index.html")) {
            abort(404);
        }

        return view('game-display', [
            'gameUrl' => asset("storage/{$gamePath}/index.html"),
            'gameId' => $id
        ]);
    }

    // Handle game feedback and regeneration
    public function updateGamePhase(Request $request, $id)
    {
        $request->validate([
            'feedback' => 'required|array'
        ]);

        Log::debug('Updating game', [
            'game_id' => $id
        ]);

        return $this->generateGamePhase($request->merge([
            'game_id' => $id
        ]));
    }

    // Store game files
    private function storeGameFiles($gameId, $gameContent)
    {
        Storage::disk('public')->put(
            "games/{$gameId}/index.html",
            $gameContent['html'] ?? ''
        );
        Storage::disk('public')->put(
            "games/{$gameId}/style.css",
            $gameContent['css'] ?? ''
        );
        Storage::disk('public')->put(
            "games/{$gameId}/script.js",
            $gameContent['js'] ?? ''
        );
    }

    // Format feedback into a structured prompt
    private function formatFeedback($feedback)
    {
        if (empty($feedback)) {
            return null;
        }

        $formatted = [];
        foreach ($feedback as $category => $content) {
            if (!empty($content)) {
                $formatted[] = ucfirst($category) . " feedback: " . $content;
            }
        }

        return implode("\n", $formatted);
    }
}
