<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — Campus Space</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-md p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-indigo-900">🚀 Campus Space</h1>
            <p class="text-sm text-gray-500 mt-1">Masuk ke akun Anda</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('email') border-red-400 @enderror">
                @error('email')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input id="password" type="password" name="password" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
                <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 text-indigo-600 border-gray-300 rounded">
                <label for="remember_me" class="ml-2 text-sm text-gray-600">Ingat saya</label>
            </div>

            <button type="submit"
                class="w-full py-2.5 bg-indigo-700 hover:bg-indigo-800 text-white text-sm font-semibold rounded-lg transition-colors">
                Masuk
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-indigo-600 font-medium hover:underline">Daftar sekarang</a>
        </p>
    </div>
</body>
</html>
