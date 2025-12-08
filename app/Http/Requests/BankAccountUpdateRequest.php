<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BankAccountUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('banking.edit');
    }

    public function rules(): array
    {
        $accountId = $this->route('account') ? $this->route('account')->id : 'NULL';

        return [
            'account_name' => ['sometimes', 'required', 'string', 'max:255'],
            'account_number' => ['sometimes', 'required', 'string', 'max:50', 'unique:bank_accounts,account_number,' . $accountId],
            'bank_name' => ['sometimes', 'required', 'string', 'max:255'],
            'branch_name' => ['nullable', 'string', 'max:255'],
            'currency' => ['sometimes', 'required', 'string', 'max:3'],
            'account_type' => ['sometimes', 'required', 'in:checking,savings,business,other'],
            'iban' => ['nullable', 'string', 'max:50'],
            'swift_code' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ];
    }
}
