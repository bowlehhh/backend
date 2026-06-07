<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDashboardProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'category' => 'Alat Berat',
            'barcode' => strtoupper((string) $this->input('barcode', '')),
            'unit' => strtoupper((string) $this->input('unit', '')),
            'weight_unit' => strtolower(trim((string) $this->input('weight_unit', 'kg'))),
            'weight_unit_custom' => trim((string) $this->input('weight_unit_custom', '')),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Product|null $product */
        $product = $this->route('product');
        $batchId = $product?->latestBatch?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($product?->id)],
            'barcode' => ['nullable', 'string', 'max:100'],
            'unit' => ['nullable', 'string', 'max:30'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'weight_unit' => ['nullable', Rule::in(['gram', 'kg', 'ton', 'lb', 'oz', 'other'])],
            'weight_unit_custom' => ['nullable', 'string', 'max:30', 'required_if:weight_unit,other'],
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
            'batch_code' => ['nullable', 'string', 'max:255', Rule::unique('product_batches', 'batch_code')->ignore($batchId)],
            'condition' => ['nullable', 'string', 'max:120'],
            'processed_by' => ['nullable', 'string', 'max:255'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'expedition_cost' => ['nullable', 'numeric', 'min:0'],
            'down_payment_amount' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'payment_type' => ['nullable', Rule::in(['LUNAS', 'KREDIT'])],
            'credit_days' => ['nullable', 'integer', 'min:1', 'max:3650', 'required_if:payment_type,KREDIT'],
            'credit_due_date' => ['nullable', 'date', 'required_if:payment_type,KREDIT'],
            'expired_at' => ['nullable', 'date'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (strtoupper((string) $this->input('payment_type', 'LUNAS')) !== 'KREDIT') {
                return;
            }

            $purchasePrice = (float) $this->input('purchase_price', 0);
            $expeditionCost = (float) $this->input('expedition_cost', 0);
            $stock = (int) $this->input('stock', 0);
            $totalPurchase = max(0, ($purchasePrice * $stock) + $expeditionCost);
            $downPayment = (float) $this->input('down_payment_amount', 0);

            if ($downPayment > $totalPurchase) {
                $validator->errors()->add('down_payment_amount', 'DP tidak boleh lebih besar dari total harga beli.');
            }
        });
    }
}
