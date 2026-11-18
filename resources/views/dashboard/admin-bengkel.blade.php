@extends('layouts.app')

@section('title', 'Dashboard Admin Bengkel')

@section('content')
    @if (auth()->user()->status === 'pending_approval')
        <div class="mb-4 border border-yellow-300 bg-yellow-50 text-yellow-800 text-sm rounded px-4 py-3">
            Akun Anda menunggu verifikasi Superadmin. Bengkel Anda belum tampil di katalog publik.
        </div>
    @endif

    <h2 class="text-lg font-semibold">Dashboard Admin Bengkel</h2>
    <p class="text-sm text-gray-500 mb-4">Halo, {{ auth()->user()->name }}.</p>

    <a href="{{ route('admin_bengkel.workshops.index') }}"
       class="block bg-white border border-gray-200 rounded p-4 hover:bg-gray-50 max-w-md">
        <div class="font-medium">Bengkel Saya</div>
        <div class="text-sm text-gray-500">Kuota terpakai: {{ $used }} dari {{ $quota }}</div>
    </a>
@endsection
