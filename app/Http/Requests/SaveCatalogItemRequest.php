<?php

namespace App\Http\Requests;

use App\Models\ServiceCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCatalogItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $workshop = $this->route('workshop');

        // 404 agar keberadaan bengkel milik admin lain tidak terbuka.
        abort_unless($workshop && $workshop->admin_user_id === auth()->id(), 404);

        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'item_name' => trim(preg_replace('/\s+/', ' ', (string) $this->input('item_name'))),
        ]);
    }

    public function rules(): array
    {
        $workshop = $this->route('workshop');

        return [
            'item_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('service_catalogs', 'item_name')
                    ->where('workshop_id', $workshop->id)
                    ->ignore($this->route('catalog')?->id),
            ],
            'category'  => ['required', Rule::in(ServiceCatalog::CATEGORIES)],
            'price'     => ['required', 'integer', 'min:0', 'max:100000000'],
        ];
    }

    public function messages(): array
    {
        return [
            'item_name.required' => 'Nama layanan atau suku cadang wajib diisi.',
            'item_name.max'      => 'Nama maksimal 100 karakter.',
            'item_name.unique'   => 'Nama ini sudah ada di katalog bengkel Anda.',
            'category.required'  => 'Pilih kategori.',
            'category.in'        => 'Kategori tidak valid.',
            'price.required'     => 'Harga wajib diisi (isi 0 jika gratis).',
            'price.integer'      => 'Harga harus berupa angka bulat (tanpa titik atau koma).',
            'price.min'          => 'Harga tidak boleh negatif.',
            'price.max'          => 'Harga maksimal Rp 100.000.000.',
        ];
    }
}
