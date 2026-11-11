<?php

namespace App\Http\Requests;

use App\Models\OilType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Rapikan plat nomor: huruf besar dan satu spasi antar bagian. */
    protected function prepareForValidation(): void
    {
        $plate = strtoupper(trim(preg_replace('/\s+/', ' ', (string) $this->input('license_plate'))));

        $this->merge(['license_plate' => $plate]);
    }

    public function rules(): array
    {
        $oilChoices = OilType::pluck('id')->push('custom')->all();

        return [
            'category_id'      => ['required', 'exists:vehicle_categories,id'],
            'model_name'       => [
                'required',
                // Model harus milik kategori yang dipilih.
                Rule::exists('vehicle_specs', 'model_name')
                    ->where(fn ($query) => $query->where('category_id', $this->input('category_id'))),
            ],
            'license_plate'    => ['required', 'string', 'max:12', 'unique:vehicles,license_plate'],
            'current_odometer' => ['required', 'integer', 'min:0', 'max:999999'],
            'oil_choice'       => ['required', Rule::in($oilChoices)],
            'custom_interval'  => ['required_if:oil_choice,custom', 'nullable', 'integer', 'min:500', 'max:20000'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required'        => 'Pilih kategori motor.',
            'category_id.exists'          => 'Kategori motor tidak valid.',
            'model_name.required'         => 'Pilih model motor.',
            'model_name.exists'           => 'Model motor tidak sesuai dengan kategori yang dipilih.',
            'license_plate.required'      => 'Plat nomor wajib diisi.',
            'license_plate.max'           => 'Plat nomor maksimal 12 karakter.',
            'license_plate.unique'        => 'Plat nomor sudah terdaftar.',
            'current_odometer.required'   => 'Odometer wajib diisi.',
            'current_odometer.integer'    => 'Odometer harus berupa angka bulat.',
            'current_odometer.min'        => 'Odometer tidak boleh negatif.',
            'current_odometer.max'        => 'Odometer maksimal 999.999 km.',
            'oil_choice.required'         => 'Pilih jenis oli.',
            'oil_choice.in'               => 'Jenis oli tidak valid.',
            'custom_interval.required_if' => 'Isi interval km untuk oli custom.',
            'custom_interval.integer'     => 'Interval harus berupa angka bulat.',
            'custom_interval.min'         => 'Interval minimal 500 km.',
            'custom_interval.max'         => 'Interval maksimal 20.000 km.',
        ];
    }
}
