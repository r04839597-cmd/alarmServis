@extends('layouts.guest')

@section('title', 'Masuk')
@section('subtitle', 'Masuk menggunakan nomor HP')

@section('content')
    <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
        @csrf

        <div>
            <label for="phone_number" class="label">Nomor HP</label>
            <input id="phone_number" name="phone_number" type="tel" class="input"
                   value="{{ old('phone_number') }}" placeholder="081234567890" required autofocus>
            @error('phone_number') <p class="err">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="label">Password</label>
            <input id="password" name="password" type="password" class="input" required>
            @error('password') <p class="err">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn">Masuk</button>
    </form>

    <p class="text-sm text-center text-gray-500 mt-4">
        Belum punya akun? <a href="{{ route('register') }}" class="text-blue-600">Daftar</a>
    </p>
@endsection
