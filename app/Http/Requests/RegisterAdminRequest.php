<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterAdminRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|max:255',
            'password_confirmation' => 'required|min:6|max:255|same:password',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Trường yêu cầu',
            'email' => 'Định dạng phải là Email',
            'max' => 'Tối đa 255 kí tự',
            'min' => 'Tối thiểu 6 kí tự',
            'unique' => 'Tài khoản đã tồn tại',
            'same' => 'Mật khẩu không khớp'
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
