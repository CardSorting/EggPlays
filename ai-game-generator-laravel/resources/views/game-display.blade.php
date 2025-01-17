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

            <h2 class="text-xl font-bold mb-4">Provide Feedback for Phase {{ $phase + 1 }}</h2>
            <form action="{{ route('game.update', ['id' => $gameId]) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="feedback" class="block text-sm font-medium text-gray-300">
                        Your Feedback
                    </label>
                    <textarea id="feedback" name="feedback" required
                        class="mt-1 w-full p-2 bg-gray-700 text-white rounded"
                        rows="4"
                        placeholder="Enter your feedback for the next phase..."></textarea>
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