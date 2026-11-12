<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => ['required', 'string', 'email', 'exists:users,email'],
            'otp' => ['required', 'string', 'size:6'],
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'حقل البريد الإلكتروني مطلوب',
            'email.exists' => 'البريد الإلكتروني غير مسجل',
            'otp.required' => 'حقل رمز التحقق مطلوب',
            'otp.size' => 'رمز التحقق يجب أن يكون مكونًا من 6 أرقام',
        ];
    }
}
