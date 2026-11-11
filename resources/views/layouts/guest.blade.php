<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Alarm Servis Motor')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style type="text/tailwindcss">
        .label { @apply block text-sm mb-1; }
        .input { @apply w-full border border-gray-300 rounded px-3 py-2 text-sm; }
        .btn   { @apply w-full bg-blue-600 text-white rounded px-4 py-2 text-sm hover:bg-blue-700; }
        .err   { @apply text-xs text-red-600 mt-1; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 p-4">
    <div class="max-w-sm mx-auto mt-10">
        <h1 class="text-xl font-semibold text-center">Alarm Servis Motor</h1>
        <p class="text-sm text-gray-500 text-center mb-6">@yield('subtitle')</p>

        <div class="bg-white border border-gray-200 rounded p-5">
            @yield('content')
        </div>
    </div>
</body>
</html>
