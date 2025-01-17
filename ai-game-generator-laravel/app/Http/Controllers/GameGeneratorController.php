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
            'theme' => 'required|string',
            'complexity' => 'required|string|in:simple,medium,complex',
            'phase' => 'sometimes|integer|min:1|max:5',
            'feedback' => 'sometimes|string'
        ]);

        try {
            $gameId = $request->game_id ?? Str::uuid();
            $phase = $request->phase ?? 1;
            
            Log::debug('Generating game phase', [
                'game_id' => $gameId,
                'phase' => $phase,
                'theme' => $request->theme
            ]);

            // Generate phase content
            $phaseContent = $this->gameService->generateGamePhase(
                $request->game_type,
                $request->theme,
                $request->complexity,
                $request->feedback
            );

            // Store phase files
            $this->storePhaseFiles($gameId, $phase, $phaseContent);

            Log::debug('Game phase stored', [
                'game_id' => $gameId,
                'phase' => $phase
            ]);

            return redirect()->route('game.show', ['id' => $gameId])
                ->with('success', 'Game phase generated successfully!')
                ->with('phase', $phase)
                ->with('next_prompt', $phaseContent['next_prompt']);

        } catch (Exception $e) {
            Log::error('Game phase generation failed', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return back()->withInput()
                ->withErrors(['error' => 'Game phase generation failed: ' . $e->getMessage()]);
        }
    }

    // Show generated game
    public function showGame($id)
    {
        $gamePath = "games/{$id}";
        
        if (!Storage::disk('public')->exists("{$gamePath}/index.html")) {
            abort(404);
        }

        $phase = session('phase', 1);
        $nextPrompt = session('next_prompt');
        
        Log::debug('Showing game', [
            'game_id' => $id,
            'phase' => $phase
        ]);

        return view('game-display', [
            'gameUrl' => asset("storage/{$gamePath}/index.html"),
            'gameId' => $id,
            'phase' => $phase,
            'nextPrompt' => $nextPrompt
        ]);
    }

    // Handle game feedback and next phase
    public function updateGamePhase(Request $request, $id)
    {
        $request->validate([
            'feedback' => 'required|string',
            'phase' => 'required|integer|min:1|max:5'
        ]);

        Log::debug('Updating game phase', [
            'game_id' => $id,
            'phase' => $request->phase,
            'new_phase' => $request->phase + 1
        ]);

        return $this->generateGamePhase($request->merge([
            'game_id' => $id,
            'theme' => $request->feedback,
            'phase' => $request->phase + 1
        ]));
    }

    // Store phase-specific files
    private function storePhaseFiles($gameId, $phase, $phaseContent)
    {
        $basePath = "games/{$gameId}/phase-{$phase}";
        
        // Store phase description
        Storage::disk('public')->put(
            "{$basePath}/description.txt",
            $phaseContent['content']['description'] ?? ''
        );
        
        // Store phase assets
        Storage::disk('public')->put(
            "{$basePath}/assets.json",
            json_encode($phaseContent['content']['assets'] ?? [])
        );
        
        // Store phase tasks
        Storage::disk('public')->put(
            "{$basePath}/tasks.json",
            json_encode($phaseContent['content']['tasks'] ?? [])
        );
        
        // If final phase, store game files
        if ($phase === 5) {
            Storage::disk('public')->put(
                "games/{$gameId}/index.html",
                $phaseContent['content']['html'] ?? ''
            );
            Storage::disk('public')->put(
                "games/{$gameId}/style.css",
                $phaseContent['content']['css'] ?? ''
            );
            Storage::disk('public')->put(
                "games/{$gameId}/script.js",
                $phaseContent['content']['js'] ?? ''
            );
        }
    }
}
