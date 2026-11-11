<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateOdometerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $vehicle = $this->route('vehicle');

        // 404 agar keberadaan motor milik user lain tidak terbuka.
        abort_unless($vehicle && $vehicle->user_id === auth()->id(), 404);

        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_meter_replaced' => $this->boolean('is_meter_replaced'),
        ]);
    }

    public function rules(): array
    {
        return [
            'odometer'          => ['required', 'integer', 'min:0', 'max:999999'],
            'is_meter_replaced' => ['boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->has('odometer') || $this->boolean('is_meter_replaced')) {
                    return;
                }

                $current = (int) $this->route('vehicle')->current_odometer;

                if ((int) $this->input('odometer') < $current) {
                    $validator->errors()->add(
                        'odometer',
                        'Odometer baru tidak boleh lebih kecil dari odometer terakhir ('
                        . number_format($current, 0, ',', '.')
                        . ' km). Jika Anda mengganti spidometer, centang opsi di bawah.'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'odometer.required' => 'Odometer wajib diisi.',
            'odometer.integer'  => 'Odometer harus berupa angka bulat.',
            'odometer.min'      => 'Odometer tidak boleh negatif.',
            'odometer.max'      => 'Odometer maksimal 999.999 km.',
        ];
    }
}
