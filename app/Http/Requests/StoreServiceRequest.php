<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
{
    /** Bag terpisah agar error servis tidak tercampur dengan error form odometer. */
    protected $errorBag = 'service';

    public function authorize(): bool
    {
        $vehicle = $this->route('vehicle');

        // 404 agar keberadaan motor milik user lain tidak terbuka.
        abort_unless($vehicle && $vehicle->user_id === auth()->id(), 404);

        return true;
    }

    public function rules(): array
    {
        $vehicle = $this->route('vehicle');

        return [
            'components'          => ['required', 'array', 'min:1'],
            'components.*'        => [
                'integer',
                Rule::exists('vehicle_components', 'id')->where('vehicle_id', $vehicle->id),
            ],
            'service_date'        => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'odometer_at_service' => ['required', 'integer', 'min:0', 'max:' . (int) $vehicle->current_odometer],
            'total_cost'          => ['required', 'integer', 'min:0', 'max:100000000'],
            'notes'               => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        $current = number_format((int) $this->route('vehicle')->current_odometer, 0, ',', '.');

        return [
            'components.required'          => 'Pilih minimal satu komponen yang diservis.',
            'components.min'               => 'Pilih minimal satu komponen yang diservis.',
            'components.*.exists'          => 'Komponen yang dipilih tidak valid.',
            'service_date.required'        => 'Tanggal pengerjaan wajib diisi.',
            'service_date.date_format'     => 'Format tanggal tidak valid.',
            'service_date.before_or_equal' => 'Tanggal pengerjaan tidak boleh di masa depan.',
            'odometer_at_service.required' => 'Odometer saat servis wajib diisi.',
            'odometer_at_service.integer'  => 'Odometer saat servis harus berupa angka bulat.',
            'odometer_at_service.min'      => 'Odometer saat servis tidak boleh negatif.',
            'odometer_at_service.max'      => "Odometer saat servis tidak boleh melebihi odometer motor saat ini ({$current} km). Perbarui odometer dulu jika perlu.",
            'total_cost.required'          => 'Total biaya wajib diisi (isi 0 jika gratis).',
            'total_cost.integer'           => 'Total biaya harus berupa angka bulat (tanpa titik atau koma).',
            'total_cost.min'               => 'Total biaya tidak boleh negatif.',
            'total_cost.max'               => 'Total biaya maksimal Rp 100.000.000.',
            'notes.max'                    => 'Catatan maksimal 255 karakter.',
        ];
    }
}
