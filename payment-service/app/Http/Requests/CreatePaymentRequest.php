<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0.50',
            'currency' => 'sometimes|string|size:3|in:eur,usd,gbp,cad',
            'user_id' => 'required|integer|min:1',
            'order_id' => 'sometimes|integer|min:1',
            'customer_email' => 'sometimes|email|max:255',
            'description' => 'sometimes|string|max:500',
            'metadata' => 'sometimes|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Le montant est requis',
            'amount.min' => 'Le montant minimum est de 0.50',
            'user_id.required' => 'L\'ID utilisateur est requis',
            'currency.in' => 'La devise doit être EUR, USD, GBP ou CAD',
        ];
    }
}
