<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class PasswordRequest extends FormRequest
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
            'old_password' => 'required',
            'new_password' => [
                'required',
                'min:6',
                'regex:/^(?=.*[A-Z]).+$/'
            ],
            'repeat_password' => [
                'required',
                'same:new_password'
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'required' => __('validation.required'),
            'min:6' => 'Phải nhập ít nhất 6 ký tự!',
            'regex:/^(?=.*[A-Z]).+$/' => 'Phải có ít nhất 1 ký tự viết hoa!',
            'same:new_password' => 'Mật Khẩu cũ và mật Khẩu mới không trùng khớp!',
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
