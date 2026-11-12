<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
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
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'حقل البريد الإلكتروني مطلوب',
            'email.exists' => 'البريد الإلكتروني غير مسجل',
            'otp.required' => 'حقل رمز التحقق مطلوب',
            'otp.size' => 'رمز التحقق يجب أن يكون مكونًا من 6 أرقام',
            'password.required' => 'حقل كلمة المرور مطلوب',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
            'password.min' => 'كلمة المرور يجب أن تكون على الأقل 8 أحرف',
            'password.mixedCase' => 'كلمة المرور يجب أن تحتوي على أحرف كبيرة وصغيرة',
            'password.numbers' => 'كلمة المرور يجب أن تحتوي على أرقام',
        ];
    }
}
