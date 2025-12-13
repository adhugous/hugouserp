<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContractStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('rental.contracts.create') ?? false;
    }

    public function rules(): array
    {
        $branchId = $this->route('branch')?->id;

        return [
            'unit_id' => [
                'required',
                'exists:rental_units,id',
                function ($attribute, $value, $fail) use ($branchId) {
                    if ($branchId) {
                        $unitExists = \App\Models\RentalUnit::where('id', $value)
                            ->whereHas('property', function ($q) use ($branchId) {
                                $q->where('branch_id', $branchId);
                            })
                            ->exists();

                        if (! $unitExists) {
                            $fail(__('The selected unit does not belong to this branch.'));
                        }
                    }
                },
            ],
            'tenant_id' => [
                'required',
                'exists:tenants,id',
                function ($attribute, $value, $fail) use ($branchId) {
                    if ($branchId) {
                        $tenantExists = \App\Models\Tenant::where('id', $value)
                            ->where('branch_id', $branchId)
                            ->exists();

                        if (! $tenantExists) {
                            $fail(__('The selected tenant does not belong to this branch.'));
                        }
                    }
                },
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'rent' => ['required', 'numeric', 'gt:0'],
        ];
    }
}
