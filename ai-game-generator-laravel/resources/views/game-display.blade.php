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
        .feedback-form {
            max-width: 800px;
            margin: 0 auto;
        }
    </style>
</head>
<body class="bg-gray-900">
    <div class="game-container">
        <iframe src="{{ $gameUrl }}" title="Generated Game"></iframe>
    </div>
    
    <div class="feedback-container">
        <div class="feedback-form">
            <h2 class="text-xl font-bold mb-4">Provide Feedback</h2>
            <form action="{{ route('game.update', ['id' => $gameId]) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Gameplay</label>
                        <textarea name="feedback[gameplay]" 
                            class="w-full p-2 bg-gray-700 text-white rounded"
                            rows="3"
                            placeholder="Controls, mechanics, difficulty..."
                            required></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Visuals</label>
                        <textarea name="feedback[visuals]"
                            class="w-full p-2 bg-gray-700 text-white rounded"
                            rows="3"
                            placeholder="Graphics, animations, UI..."
                            required></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Audio</label>
                        <textarea name="feedback[audio]"
                            class="w-full p-2 bg-gray-700 text-white rounded"
                            rows="3"
                            placeholder="Music, sound effects..."
                            required></textarea>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Update Game
                    </button>
                    <a href="/"
                        class="ml-2 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Create New Game
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>