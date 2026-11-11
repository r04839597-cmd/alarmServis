@extends('layouts.app')

@section('title', 'Modifikasi Motor')

@section('content')
    <a href="{{ route('user.vehicles.show', $vehicle) }}" class="text-sm text-blue-600">&larr; {{ $vehicle->name }}</a>

    <h2 class="text-lg font-semibold mt-2">Modifikasi Motor</h2>
    <p class="text-sm text-gray-500 mb-1">
        {{ $vehicle->name }} &middot; {{ $vehicle->license_plate }} &middot; {{ $vehicle->category->category_name }}
    </p>
    <p class="text-sm text-gray-500 mb-4">
        Biarkan "Standar" untuk bagian yang tidak diubah. Modifikasi mesin, knalpot, atau pengapian/filter
        memperpendek interval Oli Mesin menjadi 80%.
        <a href="{{ route('user.vehicles.modifications.history', $vehicle) }}" class="text-blue-600">Riwayat modifikasi</a>
    </p>

    @if ($warnings)
        <div class="mb-4 border border-yellow-300 bg-yellow-50 text-yellow-800 text-sm rounded px-4 py-3">
            <ul class="list-disc pl-4">
                @foreach ($warnings as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 border border-red-300 bg-red-50 text-red-700 text-sm rounded px-4 py-3">
            Ada isian yang perlu diperbaiki. Periksa kolom yang bertanda merah di bawah.
        </div>
    @endif

    <form method="POST" action="{{ route('user.vehicles.modifications.update', $vehicle) }}" class="max-w-xl">
        @csrf

        @foreach ($groups as $groupName => $groupTypes)
            @php
                $modifiedCount = $groupTypes->filter(fn ($type) => $installed->has($type->id))->count();
            @endphp

            <details class="bg-white border border-gray-200 rounded mb-3" {{ ($modifiedCount > 0 || $errors->any()) ? 'open' : '' }}>
                <summary class="p-3 cursor-pointer font-medium">
                    {{ $groupName }}
                    <span class="text-sm text-gray-500 font-normal">
                        ({{ $modifiedCount > 0 ? $modifiedCount . ' dimodifikasi' : 'standar' }})
                    </span>
                </summary>

                @foreach ($groupTypes as $type)
                    @php
                        $row = $installed->get($type->id);
                        $current = $row ? ($row->is_custom ? '__custom' : $row->value) : '';
                        $selected = (string) old('parts.' . $type->id, $current);
                        $customValue = old('custom.' . $type->id, ($row && $row->is_custom) ? $row->value : '');
                    @endphp

                    <div class="p-3 border-t border-gray-200">
                        <label for="part-{{ $type->id }}" class="label">
                            {{ $type->name }}
                            <span class="text-gray-400">(Standar: {{ $standards[$type->id] }})</span>
                        </label>

                        <select id="part-{{ $type->id }}" name="parts[{{ $type->id }}]" class="input"
                                data-part="{{ $type->id }}">
                            <option value="">Standar (tidak diubah)</option>
                            @foreach ($type->options as $option)
                                <option value="{{ $option->label }}" @selected($selected === $option->label)>
                                    {{ $option->label }}
                                </option>
                            @endforeach
                            <option value="__custom" @selected($selected === '__custom')>Lainnya (isi manual)</option>
                        </select>

                        <div id="custom-{{ $type->id }}" class="mt-2 hidden">
                            <input type="text" name="custom[{{ $type->id }}]" maxlength="60" class="input"
                                   placeholder="Isi manual (merek / ukuran)" value="{{ $customValue }}">
                        </div>

                        @error('parts.' . $type->id) <p class="err">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </details>
        @endforeach

        <div class="flex items-center gap-4 mt-4">
            <button type="submit" class="btn">Simpan Modifikasi</button>
            <a href="{{ route('user.vehicles.show', $vehicle) }}" class="text-sm text-gray-500">Batal</a>
        </div>
    </form>

    <script>
        document.querySelectorAll('select[data-part]').forEach(function (select) {
            const wrapper = document.getElementById('custom-' + select.dataset.part);

            function toggle() {
                wrapper.classList.toggle('hidden', select.value !== '__custom');
            }

            select.addEventListener('change', toggle);
            toggle();
        });
    </script>
@endsection
