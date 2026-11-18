<?php

namespace App\Http\Requests;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkshopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Rapikan nomor telepon ke format 628... sebelum validasi. */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone_number' => PhoneNumber::sanitize($this->input('phone_number')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:100'],
            'address'       => ['required', 'string', 'max:255'],
            'phone_number'  => ['required', 'regex:/^628[0-9]{8,12}$/'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'opening_hours' => ['nullable', 'string', 'max:150'],
            'latitude'      => ['nullable', 'numeric', 'between:-11,6', 'required_with:longitude'],
            'longitude'     => ['nullable', 'numeric', 'between:95,141', 'required_with:latitude'],
            'photo'         => ['bail', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'          => 'Nama bengkel wajib diisi.',
            'name.max'               => 'Nama bengkel maksimal 100 karakter.',
            'address.required'       => 'Alamat bengkel wajib diisi.',
            'address.max'            => 'Alamat maksimal 255 karakter.',
            'phone_number.required'  => 'Nomor telepon/WhatsApp bengkel wajib diisi.',
            'phone_number.regex'     => 'Format nomor tidak valid. Contoh: 081234567890.',
            'description.max'        => 'Deskripsi maksimal 1.000 karakter.',
            'opening_hours.max'      => 'Jam buka maksimal 150 karakter.',
            'latitude.numeric'       => 'Lokasi di peta tidak valid.',
            'latitude.between'       => 'Lokasi harus berada di wilayah Indonesia.',
            'latitude.required_with' => 'Lokasi di peta tidak lengkap. Klik peta lagi atau hapus pin.',
            'longitude.numeric'      => 'Lokasi di peta tidak valid.',
            'longitude.between'      => 'Lokasi harus berada di wilayah Indonesia.',
            'longitude.required_with' => 'Lokasi di peta tidak lengkap. Klik peta lagi atau hapus pin.',
            'photo.uploaded'         => 'Foto gagal diunggah. Coba lagi dengan file lain, atau periksa pengaturan unggahan server.',
            'photo.image'            => 'File harus berupa gambar (JPG, PNG, atau WEBP).',
            'photo.mimes'            => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'photo.max'              => 'Ukuran foto maksimal 2 MB.',
        ];
    }
}
