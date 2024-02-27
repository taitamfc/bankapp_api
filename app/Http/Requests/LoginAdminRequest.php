<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginAdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function rules(): array
    {
        return [
            'phone' => 'required|max:255',
            'password' => 'required|max:255',
        ];
    }
    public function messages()
    {
        return [
            'required' => 'Trường yêu cầu',
            // 'email' => 'Yêu cầu định dạng là Email',
            'max' => 'Tối đa 255 kí tự'
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
