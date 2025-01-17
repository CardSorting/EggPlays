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
    </style>
</head>
<body class="bg-gray-900">
    <div class="game-container">
        <iframe src="{{ $gameUrl }}" title="Generated Game"></iframe>
    </div>
    
    <div class="feedback-container">
        <h2 class="text-xl font-bold mb-4">Game Feedback (Iteration {{ $iteration }})</h2>
        <form action="{{ route('game.update', ['id' => $gameId]) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="feedback" class="block text-sm font-medium text-gray-300">
                    How can we improve this game?
                </label>
                <textarea id="feedback" name="feedback" required
                    class="mt-1 w-full p-2 bg-gray-700 text-white rounded"
                    rows="3"
                    placeholder="Enter your feedback here..."></textarea>
            </div>
            <input type="hidden" name="iteration" value="{{ $iteration }}">
            <div>
                <button type="submit"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Submit Feedback and Improve Game
                </button>
            </div>
        </form>
    </div>
</body>
</html>