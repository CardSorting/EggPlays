<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Play Generated Game</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .game-container {
            position: relative;
            width: 100%;
            height: 80vh;
            overflow: hidden;
        }
        iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        .feedback-container {
            padding: 2rem;
            background: #1a202c;
            color: white;
        }
        .phase-progress {
            margin-bottom: 2rem;
        }
        .feedback-categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .feedback-category {
            background: #2d3748;
            padding: 1rem;
            border-radius: 0.5rem;
        }
        .change-log {
            background: #2d3748;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 2rem;
        }
    </style>
</head>
<body class="bg-gray-900">
    <div class="game-container">
        <iframe src="{{ $gameUrl }}" title="Generated Game"></iframe>
    </div>
    
    <div class="feedback-container">
        @if ($phase < 5)
            <div class="phase-progress">
                <h2 class="text-xl font-bold mb-2">Development Phase {{ $phase }} of 5</h2>
                <div class="flex justify-between mb-2">
                    <span class="text-sm font-medium">{{ $phase * 20 }}% Complete</span>
                    <span class="text-sm font-medium">Next: Phase {{ $phase + 1 }}</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2.5">
                    <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $phase * 20 }}%"></div>
                </div>
            </div>

            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-2">Next Phase Instructions</h3>
                <div class="bg-gray-800 p-4 rounded">
                    <p class="text-gray-300">{{ $nextPrompt }}</p>
                </div>
            </div>

            <h2 class="text-xl font-bold mb-4">Provide Detailed Feedback</h2>
            <form action="{{ route('game.update', ['id' => $gameId]) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div class="feedback-categories">
                    <div class="feedback-category">
                        <h4 class="font-semibold mb-2">Gameplay</h4>
                        <textarea name="feedback[gameplay]" 
                            class="w-full p-2 bg-gray-700 text-white rounded"
                            rows="3"
                            placeholder="Controls, mechanics, difficulty..."></textarea>
                    </div>
                    <div class="feedback-category">
                        <h4 class="font-semibold mb-2">Visuals</h4>
                        <textarea name="feedback[visuals]"
                            class="w-full p-2 bg-gray-700 text-white rounded"
                            rows="3"
                            placeholder="Graphics, animations, UI..."></textarea>
                    </div>
                    <div class="feedback-category">
                        <h4 class="font-semibold mb-2">Audio</h4>
                        <textarea name="feedback[audio]"
                            class="w-full p-2 bg-gray-700 text-white rounded"
                            rows="3"
                            placeholder="Music, sound effects..."></textarea>
                    </div>
                    <div class="feedback-category">
                        <h4 class="font-semibold mb-2">Story</h4>
                        <textarea name="feedback[story]"
                            class="w-full p-2 bg-gray-700 text-white rounded"
                            rows="3"
                            placeholder="Narrative, characters..."></textarea>
                    </div>
                </div>

                <div class="change-log">
                    <h4 class="font-semibold mb-2">Changes Since Last Version</h4>
                    <ul class="list-disc list-inside text-gray-300">
                        @foreach ($changes as $change)
                            <li>{{ $change }}</li>
                        @endforeach
                    </ul>
                </div>

                <input type="hidden" name="phase" value="{{ $phase }}">
                <div>
                    <button type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Continue to Phase {{ $phase + 1 }}
                    </button>
                </div>
            </form>
        @else
            <div class="text-center">
                <h2 class="text-2xl font-bold mb-4">Game Development Complete!</h2>
                <p class="text-gray-300 mb-6">Your game is now ready to play. Thank you for your feedback throughout the development process.</p>
                <a href="/"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create Another Game
                </a>
            </div>
        @endif
    </div>
</body>
</html>