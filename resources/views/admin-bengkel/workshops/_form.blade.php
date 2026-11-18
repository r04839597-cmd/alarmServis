<div>
    <label for="name" class="label">Nama Bengkel</label>
    <input id="name" name="name" type="text" maxlength="100" class="input"
           value="{{ old('name', $workshop->name ?? '') }}" required>
    @error('name') <p class="err">{{ $message }}</p> @enderror
</div>

<div>
    <label for="address" class="label">Alamat</label>
    <textarea id="address" name="address" rows="3" maxlength="255" class="input" required>{{ old('address', $workshop->address ?? '') }}</textarea>
    @error('address') <p class="err">{{ $message }}</p> @enderror
</div>

<div>
    <label for="phone_number" class="label">Nomor Telepon / WhatsApp</label>
    <input id="phone_number" name="phone_number" type="tel" class="input"
           value="{{ old('phone_number', $workshop->phone_number ?? '') }}" placeholder="081234567890" required>
    @error('phone_number') <p class="err">{{ $message }}</p> @enderror
</div>

<div>
    <label for="description" class="label">Deskripsi (opsional)</label>
    <textarea id="description" name="description" rows="4" maxlength="1000" class="input"
              placeholder="Ceritakan layanan, keunggulan, atau spesialisasi bengkel Anda.">{{ old('description', $workshop->description ?? '') }}</textarea>
    @error('description') <p class="err">{{ $message }}</p> @enderror
</div>

<div>
    <label for="opening_hours" class="label">Jam Buka (opsional)</label>
    <input id="opening_hours" name="opening_hours" type="text" maxlength="150" class="input"
           value="{{ old('opening_hours', $workshop->opening_hours ?? '') }}"
           placeholder="Contoh: Senin sampai Sabtu, 08.00 sampai 17.00">
    @error('opening_hours') <p class="err">{{ $message }}</p> @enderror
</div>

<div>
    <span class="label">Lokasi di Peta (opsional)</span>
    <p class="text-xs text-gray-500 mb-2">Klik peta untuk menaruh pin tepat di lokasi bengkel. Pin bisa digeser dengan mengklik lokasi lain.</p>

    <div id="map" class="w-full h-64 rounded border border-gray-300"></div>

    <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $workshop->latitude ?? '') }}">
    <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $workshop->longitude ?? '') }}">

    <div class="flex flex-wrap items-center gap-3 mt-2 text-sm">
        <button type="button" id="use-my-location" class="border border-gray-300 rounded px-3 py-1 hover:bg-gray-100">Gunakan lokasi saya</button>
        <button type="button" id="clear-pin" class="text-gray-500">Hapus pin</button>
        <span id="coord-text" class="text-xs text-gray-500"></span>
    </div>

    @error('latitude') <p class="err">{{ $message }}</p> @enderror
    @error('longitude') <p class="err">{{ $message }}</p> @enderror
</div>

<div>
    <label for="photo" class="label">Foto Utama Bengkel (opsional, maksimal 2 MB)</label>

    @if (! empty($workshop?->photo))
        <img src="{{ $workshop->photoUrl() }}" alt="Foto {{ $workshop->name }}"
             class="w-full max-w-xs h-40 object-cover rounded border border-gray-200 mb-2">
        <p class="text-xs text-gray-500 mb-2">Pilih file baru untuk mengganti foto utama ini. Foto tambahan dikelola lewat menu Kelola Foto.</p>
    @endif

    <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" class="text-sm">
    @error('photo') <p class="err">{{ $message }}</p> @enderror
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
<script>
    (function () {
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const coordText = document.getElementById('coord-text');

        // Pusat awal peta: Yogyakarta.
        const defaultCenter = [-7.7956, 110.3695];
        const hasPin = latInput.value !== '' && lngInput.value !== '';
        const start = hasPin
            ? [parseFloat(latInput.value), parseFloat(lngInput.value)]
            : defaultCenter;

        const map = L.map('map').setView(start, hasPin ? 16 : 13);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        let marker = null;

        function showCoords(lat, lng) {
            coordText.textContent = 'Koordinat: ' + lat.toFixed(6) + ', ' + lng.toFixed(6);
        }

        function setPin(lat, lng, zoomIn) {
            latInput.value = lat.toFixed(7);
            lngInput.value = lng.toFixed(7);

            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng]).addTo(map);
            }

            if (zoomIn) {
                map.setView([lat, lng], 17);
            }

            showCoords(lat, lng);
        }

        function clearPin() {
            latInput.value = '';
            lngInput.value = '';

            if (marker) {
                map.removeLayer(marker);
                marker = null;
            }

            coordText.textContent = 'Belum ada pin.';
        }

        if (hasPin) {
            setPin(start[0], start[1], false);
        } else {
            coordText.textContent = 'Belum ada pin.';
        }

        map.on('click', function (event) {
            setPin(event.latlng.lat, event.latlng.lng, false);
        });

        document.getElementById('clear-pin').addEventListener('click', clearPin);

        document.getElementById('use-my-location').addEventListener('click', function () {
            if (! navigator.geolocation) {
                alert('Browser Anda tidak mendukung pencarian lokasi.');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    setPin(position.coords.latitude, position.coords.longitude, true);
                },
                function () {
                    alert('Lokasi tidak bisa diambil. Izinkan akses lokasi di browser, atau klik peta secara manual.');
                }
            );
        });
    })();
</script>
