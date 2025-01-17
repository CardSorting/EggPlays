<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Game Generator</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold mb-8">AI Game Generator</h1>
        
        <form action="{{ route('generator.create') }}" method="POST" class="space-y-6">
            @csrf
            
            @if (session('feedback'))
                <input type="hidden" name="game_id" value="{{ session('game_id') }}">
                
                <div>
                    <label for="feedback" class="block text-sm font-medium text-gray-700">
                        Your Feedback
                    </label>
                    <textarea id="feedback" name="feedback" rows="4" required
                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                        placeholder="Provide feedback to improve the game..."></textarea>
                </div>
            @else
                <div>
                    <label for="game_type" class="block text-sm font-medium text-gray-700">
                        Game Type
                    </label>
                    <select id="game_type" name="game_type" required
                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="platformer">Platformer</option>
                        <option value="puzzle">Puzzle</option>
                        <option value="shooter">Shooter</option>
                        <option value="rpg">RPG</option>
                    </select>
                </div>
            @endif

            <div>
                <button type="submit"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    @if (session('feedback'))
                        Update Game
                    @else
                        Generate Game
                    @endif
                </button>
            </div>
        </form>
    </div>
</body>
</html>