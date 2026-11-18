<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - Alarm Servis Motor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style type="text/tailwindcss">
        .label { @apply block text-sm mb-1; }
        .input { @apply w-full border border-gray-300 rounded px-3 py-2 text-sm; }
        .btn   { @apply bg-blue-600 text-white rounded px-4 py-2 text-sm hover:bg-blue-700; }
        .err   { @apply text-xs text-red-600 mt-1; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 py-3 flex justify-between items-center">
            <span class="font-semibold">Alarm Servis Motor</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <span class="text-sm text-gray-500 mr-3">{{ auth()->user()->name }}</span>
                <button type="submit" class="text-sm border border-gray-300 rounded px-3 py-1 hover:bg-gray-100">Keluar</button>
            </form>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-6">
        @if (session('success'))
            <div class="mb-4 border border-green-300 bg-green-50 text-green-800 text-sm rounded px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 border border-red-300 bg-red-50 text-red-700 text-sm rounded px-4 py-3">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
