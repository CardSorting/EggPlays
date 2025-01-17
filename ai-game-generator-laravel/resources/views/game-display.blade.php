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
            height: 100vh;
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
    </style>
</head>
<body class="bg-gray-900">
    <div class="game-container">
        <iframe src="{{ $gameUrl }}" title="Generated Game"></iframe>
    </div>
</body>
</html>