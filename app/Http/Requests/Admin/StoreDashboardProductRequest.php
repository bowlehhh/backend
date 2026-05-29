<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDashboardProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('products', 'barcode')],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:5120'],
            'category' => ['required', 'string', 'max:255'],
            'brand' => ['required', 'string', 'max:255'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'supplier_name' => ['required', 'string', 'max:255'],
            'supplier_branch' => ['nullable', 'string', 'max:255'],
            'supplier_phone' => ['nullable', 'string', 'max:255'],
            'supplier_address' => ['nullable', 'string'],
            'supplier_note' => ['nullable', 'string'],
            'batch_code' => ['nullable', 'string', 'max:255', Rule::unique('product_batches', 'batch_code')],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'expired_at' => ['nullable', 'date'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
