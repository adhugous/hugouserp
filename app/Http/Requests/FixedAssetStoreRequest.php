<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FixedAssetStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('fixed-assets.create');
    }

    public function rules(): array
    {
        return [
            'asset_code' => ['required', 'string', 'max:50', 'unique:fixed_assets,asset_code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'purchase_date' => ['required', 'date'],
            'purchase_cost' => ['required', 'numeric', 'min:0'],
            'salvage_value' => ['nullable', 'numeric', 'min:0'],
            'useful_life_years' => ['nullable', 'integer', 'min:0'],
            'useful_life_months' => ['nullable', 'integer', 'min:0', 'max:11'],
            'depreciation_method' => ['required', 'in:straight_line,declining_balance,units_of_production'],
            'depreciation_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'depreciation_start_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive,disposed,under_maintenance'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'manufacturer' => ['nullable', 'string', 'max:100'],
            'warranty_expiry_date' => ['nullable', 'date'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('branch_id') && $this->user()->branch_id) {
            $this->merge([
                'branch_id' => $this->user()->branch_id,
            ]);
        }

        if (!$this->has('status')) {
            $this->merge([
                'status' => 'active',
            ]);
        }
    }
}
