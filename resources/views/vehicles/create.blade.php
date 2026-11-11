@extends('layouts.app')

@section('title', 'Tambah Motor')

@section('content')
    <h2 class="text-lg font-semibold mb-4">Tambah Motor</h2>

    <form method="POST" action="{{ route('user.vehicles.store') }}"
          class="bg-white border border-gray-200 rounded p-5 max-w-md space-y-4">
        @csrf

        <div>
            <label for="category_id" class="label">Kategori Motor</label>
            <select id="category_id" name="category_id" class="input" required>
                <option value="">-- Pilih kategori --</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                        {{ $category->category_name }}
                    </option>
                @endforeach
            </select>
            @error('category_id') <p class="err">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="model_name" class="label">Model Motor</label>
            <select id="model_name" name="model_name" class="input" required disabled>
                <option value="">-- Pilih kategori dulu --</option>
            </select>
            @error('model_name') <p class="err">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="license_plate" class="label">Plat Nomor</label>
            <input id="license_plate" name="license_plate" type="text" class="input"
                   value="{{ old('license_plate') }}" placeholder="L 1234 AB" required>
            @error('license_plate') <p class="err">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="current_odometer" class="label">Odometer Saat Ini (km)</label>
            <input id="current_odometer" name="current_odometer" type="number" min="0" class="input"
                   value="{{ old('current_odometer') }}" required>
            @error('current_odometer') <p class="err">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="oil_choice" class="label">Jenis Oli Mesin</label>
            <select id="oil_choice" name="oil_choice" class="input" required>
                <option value="">-- Pilih jenis oli --</option>
                @foreach ($oilTypes as $oil)
                    <option value="{{ $oil->id }}" @selected(old('oil_choice') == $oil->id)>
                        {{ $oil->name }} ({{ number_format($oil->default_interval_km, 0, ',', '.') }} km)
                    </option>
                @endforeach
                <option value="custom" @selected(old('oil_choice') === 'custom')>Custom</option>
            </select>
            @error('oil_choice') <p class="err">{{ $message }}</p> @enderror
        </div>

        <div id="custom_wrapper" class="hidden">
            <label for="custom_interval" class="label">Interval Oli Custom (km)</label>
            <input id="custom_interval" name="custom_interval" type="number" min="500" class="input"
                   value="{{ old('custom_interval') }}">
            @error('custom_interval') <p class="err">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn">Simpan</button>
            <a href="{{ route('user.vehicles.index') }}" class="text-sm text-gray-500">Batal</a>
        </div>
    </form>

    <script>
        const specsByCategory = @json($specsByCategory);
        const oldModel = @json(old('model_name'));

        const categorySelect = document.getElementById('category_id');
        const modelSelect = document.getElementById('model_name');
        const oilChoice = document.getElementById('oil_choice');
        const customWrapper = document.getElementById('custom_wrapper');

        function fillModels(selected) {
            const models = specsByCategory[categorySelect.value] || [];

            modelSelect.innerHTML = '';

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = categorySelect.value
                ? (models.length ? '-- Pilih model --' : '-- Belum ada model --')
                : '-- Pilih kategori dulu --';
            modelSelect.appendChild(placeholder);

            models.forEach(function (name) {
                const option = document.createElement('option');
                option.value = name;
                option.textContent = name;
                option.selected = (name === selected);
                modelSelect.appendChild(option);
            });

            modelSelect.disabled = models.length === 0;
        }

        function toggleCustom() {
            customWrapper.classList.toggle('hidden', oilChoice.value !== 'custom');
        }

        categorySelect.addEventListener('change', function () {
            fillModels(null);
        });

        oilChoice.addEventListener('change', toggleCustom);

        fillModels(oldModel);
        toggleCustom();
    </script>
@endsection
