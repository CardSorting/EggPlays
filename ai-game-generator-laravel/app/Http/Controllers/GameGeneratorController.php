<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\GameGeneratorService;
use Exception;

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
            'complexity' => 'required|string|in:simple,medium,complex'
        ]);

        try {
            // Generate unique game ID
            $gameId = Str::uuid();
            
            // Generate game files using AI
            $gameFiles = $this->gameService->generateGameCode(
                $request->game_type,
                $request->theme,
                $request->complexity
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

            return redirect()->route('game.show', ['id' => $gameId])
                ->with('success', 'Game generated successfully!');

        } catch (Exception $e) {
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
            'gameUrl' => asset("storage/{$gamePath}/index.html")
        ]);
    }
}
