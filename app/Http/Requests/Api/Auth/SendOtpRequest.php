<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => ['required', 'string', 'email', 'exists:users,email'],
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'حقل البريد الإلكتروني مطلوب',
            'email.exists' => 'البريد الإلكتروني غير مسجل',
        ];
    }
}