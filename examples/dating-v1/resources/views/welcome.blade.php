<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Spark • Modern Dating App Demo</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-950 text-slate-100 antialiased selection:bg-rose-500 selection:text-white min-h-screen">
    @livewire('dating-app')

    @livewireScripts
</body>
</html>
