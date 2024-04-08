<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,\Illuminate\Contracts\Validation\ValidationRule|array<string>|string>
     */
    public function rules(): array
    {
        return [
            "product_id" => "required|int",
            "debit_destination" => "required|string",
            "credit_destination" => "required|string",
            "amount" => "sometimes|decimal:0,2",
            "options" => "nullable|array",
            "options.*" => "integer|exists:options,id"
        ];
    }
}
