<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class ExtendRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'package_id' => 'required',
            'billing_term' => 'nullable|in:monthly,semi-annual,yearly',
            'subscription_terms_accepted' => 'required|accepted',
            'payment_method' => 'nullable|string',
            'cardNumber' => 'sometimes|required',
            'month' => 'sometimes|required',
            'year' => 'sometimes|required',
            'cardCVC' => 'sometimes|required',
        ];
    }

    public function messages(): array
    {
        return [
            'receipt.required' => 'The receipt field image is required when instruction required receipt image',
            'subscription_terms_accepted.required' => 'You must accept the package terms and disclaimer before checkout.',
            'subscription_terms_accepted.accepted' => 'You must accept the package terms and disclaimer before checkout.',
        ];
    }
}
