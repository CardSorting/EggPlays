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

    // Handle game generation
    public function generateGame(Request $request)
    {
        $request->validate([
            'game_type' => 'required|string',
            'theme' => 'required|string',
            'complexity' => 'required|string|in:simple,medium,complex',
            'iteration' => 'sometimes|integer|min:1'
        ]);

        try {
            // Generate unique game ID
            $gameId = $request->game_id ?? Str::uuid();
            $iteration = $request->iteration ?? 1;
            
            Log::debug('Generating game', [
                'game_id' => $gameId,
                'iteration' => $iteration,
                'theme' => $request->theme
            ]);

            // Generate game files using AI
            $gameFiles = $this->gameService->generateGameCode(
                $request->game_type,
                $request->theme,
                $request->complexity,
                $iteration
            );

            // Store game files
            Storage::disk('public')->put(
                "games/{$gameId}/index.html",
                $gameFiles['html']
            );
            Storage::disk('public')->put(
                "games/{$gameId}/style.css",
                $gameFiles['css']
            );
            Storage::disk('public')->put(
                "games/{$gameId}/script.js",
                $gameFiles['js']
            );

            Log::debug('Game files stored', [
                'game_id' => $gameId,
                'iteration' => $gameFiles['iteration']
            ]);

            return redirect()->route('game.show', ['id' => $gameId])
                ->with('success', 'Game generated successfully!')
                ->with('iteration', $gameFiles['iteration']);

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

        $iteration = session('iteration', 1);
        Log::debug('Showing game', [
            'game_id' => $id,
            'iteration' => $iteration
        ]);

        return view('game-display', [
            'gameUrl' => asset("storage/{$gamePath}/index.html"),
            'gameId' => $id,
            'iteration' => $iteration
        ]);
    }

    // Handle game feedback and regeneration
    public function updateGame(Request $request, $id)
    {
        $request->validate([
            'feedback' => 'required|string',
            'iteration' => 'required|integer|min:1'
        ]);

        Log::debug('Updating game', [
            'game_id' => $id,
            'iteration' => $request->iteration,
            'new_iteration' => $request->iteration + 1
        ]);

        return $this->generateGame($request->merge([
            'game_id' => $id,
            'theme' => $request->feedback,
            'iteration' => $request->iteration + 1
        ]));
    }
}
