@extends('layouts.guest')

@section('title', 'Daftar')
@section('subtitle', 'Buat akun baru')

@section('content')
    <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
        @csrf

        <div>
            <span class="label">Tipe Akun</span>
            <label class="mr-4 text-sm">
                <input type="radio" name="account_type" value="user" @checked(old('account_type', 'user') === 'user')>
                Pemilik Motor
            </label>
            <label class="text-sm">
                <input type="radio" name="account_type" value="admin_bengkel" @checked(old('account_type') === 'admin_bengkel')>
                Admin Bengkel
            </label>
            <p class="text-xs text-gray-500 mt-1">Akun Admin Bengkel perlu diverifikasi Superadmin.</p>
            @error('account_type') <p class="err">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="name" class="label">Nama</label>
            <input id="name" name="name" type="text" class="input" value="{{ old('name') }}" required>
            @error('name') <p class="err">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="phone_number" class="label">Nomor HP</label>
            <input id="phone_number" name="phone_number" type="tel" class="input"
                   value="{{ old('phone_number') }}" placeholder="081234567890 atau +6281234567890" required>
            @error('phone_number') <p class="err">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="label">Password</label>
            <input id="password" name="password" type="password" class="input" required>
            @error('password') <p class="err">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="label">Konfirmasi Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" class="input" required>
        </div>

        <button type="submit" class="btn">Daftar</button>
    </form>

    <p class="text-sm text-center text-gray-500 mt-4">
        Sudah punya akun? <a href="{{ route('login') }}" class="text-blue-600">Masuk</a>
    </p>
@endsection
