<?php

namespace App\Http\Requests\Room;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminRoomBookingRequest extends FormRequest
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
    public function rules()
    {
        if ($this->filled('booking_id')) {
            $booking_id = $this->booking_id;
        } else {
            $booking_id = null;
        }

        $isVendorEwallet = str_starts_with((string) $this->input('payment_method'), 'vendor_ewallet_');

        return [
            'price' => 'required',
            'adult' => 'required',
            'customer_name' => 'required',
            'customer_phone' => 'required',
            'customer_email' => 'required|email:rfc,dns',
            'payment_method' => 'required',
            'payment_status' => 'required',
            'payment_transaction_id' => [
                $isVendorEwallet ? 'required' : 'nullable',
                'string',
                'max:255',
                Rule::unique('bookings', 'payment_transaction_id')->ignore($booking_id),
            ],
            'payer_phone' => $isVendorEwallet ? 'required|string|max:50' : 'nullable',
        ];
    }
    public function messages()
    {
        return [
            'price.required' => __('Time slot field is required.'),
            'payment_transaction_id.required' => __('The e-wallet transaction ID is required.'),
            'payment_transaction_id.unique' => __('This e-wallet transaction ID has already been used.'),
            'payer_phone.required' => __('The payer phone number is required.'),
        ];
    }
}
