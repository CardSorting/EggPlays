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
        
        @if (session('phase'))
            <div class="mb-8">
                <div class="flex justify-between mb-2">
                    <span class="text-sm font-medium">Phase {{ session('phase') }} of 5</span>
                    <span class="text-sm font-medium">{{ session('phase') * 20 }}% Complete</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ session('phase') * 20 }}%"></div>
                </div>
            </div>
        @endif

        <form action="{{ route('generator.create') }}" method="POST" class="space-y-6">
            @csrf
            
            @if (session('phase'))
                <input type="hidden" name="phase" value="{{ session('phase') }}">
                <input type="hidden" name="game_id" value="{{ session('game_id') }}">
                
                <div>
                    <label for="feedback" class="block text-sm font-medium text-gray-700">
                        Your Feedback
                    </label>
                    <textarea id="feedback" name="feedback" rows="4" required
                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                        placeholder="Provide feedback for the next phase..."></textarea>
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

                <div>
                    <label for="theme" class="block text-sm font-medium text-gray-700">
                        Theme
                    </label>
                    <input type="text" name="theme" id="theme" required
                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                        placeholder="e.g. Space, Medieval, Cyberpunk">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Complexity
                    </label>
                    <div class="mt-1 space-y-2">
                        <div class="flex items-center">
                            <input id="simple" name="complexity" type="radio" value="simple" required
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                            <label for="simple" class="ml-3 block text-sm font-medium text-gray-700">
                                Simple
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input id="medium" name="complexity" type="radio" value="medium"
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                            <label for="medium" class="ml-3 block text-sm font-medium text-gray-700">
                                Medium
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input id="complex" name="complexity" type="radio" value="complex"
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                            <label for="complex" class="ml-3 block text-sm font-medium text-gray-700">
                                Complex
                            </label>
                        </div>
                    </div>
                </div>
            @endif

            <div>
                <button type="submit"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    @if (session('phase'))
                        Continue to Phase {{ session('phase') + 1 }}
                    @else
                        Start Game Generation
                    @endif
                </button>
            </div>
        </form>
    </div>
</body>
</html>