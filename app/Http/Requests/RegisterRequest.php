<?php

namespace App\Http\Requests;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Sanitasi dilakukan SEBELUM validasi, sehingga `unique` mengecek nomor yang sudah dinormalisasi. */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone_number' => PhoneNumber::sanitize($this->input('phone_number')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:100'],
            'account_type' => ['required', 'in:user,admin_bengkel'],
            'phone_number' => ['required', 'regex:/^628[0-9]{8,12}$/', 'unique:users,phone_number'],
            'password'     => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'Nama wajib diisi.',
            'account_type.required' => 'Pilih tipe akun.',
            'account_type.in'       => 'Tipe akun tidak valid.',
            'phone_number.required' => 'Nomor HP wajib diisi.',
            'phone_number.regex'    => 'Format nomor HP tidak valid. Contoh: 081234567890.',
            'phone_number.unique'   => 'Nomor HP sudah terdaftar.',
            'password.required'     => 'Password wajib diisi.',
            'password.min'          => 'Password minimal 8 karakter.',
            'password.confirmed'    => 'Konfirmasi password tidak cocok.',
        ];
    }
}
