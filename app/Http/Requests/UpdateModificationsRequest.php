<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateModificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $vehicle = $this->route('vehicle');

        // 404 agar keberadaan motor milik user lain tidak terbuka.
        abort_unless($vehicle && $vehicle->user_id === auth()->id(), 404);

        return true;
    }

    public function rules(): array
    {
        return [
            'parts'    => ['nullable', 'array'],
            'parts.*'  => ['nullable', 'string', 'max:100'],
            'custom'   => ['nullable', 'array'],
            'custom.*' => ['nullable', 'string', 'max:60'],
        ];
    }

    public function messages(): array
    {
        return [
            'parts.*.max'  => 'Pilihan terlalu panjang.',
            'custom.*.max' => 'Isian manual maksimal 60 karakter.',
        ];
    }
}
