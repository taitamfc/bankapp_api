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
            'user_name' => [
                'required',
                'max:255',
                'unique:users',
                'regex:/^[A-Za-z0-9]+$/u',
            ],
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|max:255',
            'referral_code' => 'nullable|exists:users,user_name',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Trường yêu cầu',
            'email' => 'Định dạng phải là Email',
            'max' => 'Tối đa 255 kí tự',
            'min' => 'Tối thiểu 6 kí tự',
            'email.unique' => 'Tài khoản đã tồn tại',
            'referral_code.*' => 'Mã giới thiệu không hợp lệ',
            'user_name.regex' => 'Tài khoản đăng nhập không hợp lệ',
            'user_name.unique' => 'Tài khoản đăng nhập đã tồn tại'
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
