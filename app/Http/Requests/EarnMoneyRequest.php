<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class EarnMoneyRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:5000'],
            'bank_name' => 'required',
            'bank_number' => 'required',
            'bank_user' => 'required',
            'verify_code' => ['required', 'digits:6'],
        ];
    }
    public function messages(): array
    {
        return [
            'required' => __('validation.required'),
            'amount.numeric' => __('validation.numeric'),
            'amount.min:5000' => "Số tiền không được bé hơn 5000 VND",
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
            'success' => false,
            'has_errors' => true,
        ], 200));
    }
}
