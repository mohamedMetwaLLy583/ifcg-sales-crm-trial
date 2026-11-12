<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\SendOtpRequest;
use App\Http\Requests\Api\Auth\VerifyOtpRequest;
use App\Http\Requests\Api\Auth\ResetPasswordRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Log;

class AuthApiController extends Controller
{
    /**
     * تسجيل مستخدم جديد
     */
    public function register(RegisterRequest $request)
    {
        try {
            $email = $request->filled('email')
                ? $request->email
                : $this->generateLocalEmailFromPhone($request->phone);

            $user = User::create([
                'name' => $request->name,
                'email' => $email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'email_verified_at' => $request->filled('email') ? null : now(),
                'is_active' => true
            ]);

            try {
                if (!$user->hasRole('customer')) {
                    $user->assignRole('customer');
                }
            } catch (\Throwable $e) {
                Log::warning('Unable to assign customer role during API registration: ' . $e->getMessage());
            }

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'status' => true,
                'message' => 'تم التسجيل بنجاح',
                'data' => [
                    'user' => $user->only(['id', 'name', 'email', 'phone']),
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60,
                    'requires_verification' => false
                ]
            ], 201);

            // إنشاء وإرسال OTP للتحقق من البريد
            $otp = $this->generateOtp();
            $user->update([
                'otp' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(10)
            ]);

            // إرسال OTP عبر البريد الإلكتروني
            $this->sendOtpEmail($user->email, $otp, 'التحقق من البريد الإلكتروني');

            // إنشاء التوكن
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'status' => true,
                'message' => 'تم التسجيل بنجاح. تم إرسال رمز التحقق إلى بريدك الإلكتروني.',
                'data' => [
                    'user' => $user->only(['id', 'name', 'email', 'phone']),
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60,
                    'requires_verification' => true
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء التسجيل',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * تسجيل الدخول مع التعامل مع الحسابات المحذوفة وغير الفعالة
     */
    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->only('phone', 'password');

            // التحقق من وجود المستخدم أولاً
            $user = User::withTrashed()->where('phone', $request->phone)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'المستخدم غير موجود'
                ], 404);
            }

            // التحقق إذا كان الحساب محذوفاً
            if ($user->trashed()) {
                // إنشاء وإرسال OTP لاستعادة الحساب
                $otp = $this->generateOtp();
                $user->update([
                    'otp' => $otp,
                    'otp_expires_at' => Carbon::now()->addMinutes(10)
                ]);

                // إرسال OTP عبر البريد الإلكتروني
                $this->sendOtpEmail($user->email, $otp, 'استعادة الحساب');

                return response()->json([
                    'status' => false,
                    'message' => 'الحساب محذوف. تم إرسال رمز التحقق لاستعادة الحساب إلى بريدك الإلكتروني.',
                    'is_deleted' => true,
                    'requires_otp' => true,
                    'data' => [
                        'email' => $user->email,
                        'action' => 'restore_account',
                        'expires_in' => 10
                    ]
                ], 401);
            }

            // التحقق إذا كان الحساب غير فعال
            if (!$user->is_active) {
                // إنشاء وإرسال OTP لتفعيل الحساب
                $otp = $this->generateOtp();
                $user->update([
                    'otp' => $otp,
                    'otp_expires_at' => Carbon::now()->addMinutes(10)
                ]);

                // إرسال OTP عبر البريد الإلكتروني
                $this->sendOtpEmail($user->email, $otp, 'تفعيل الحساب');

                return response()->json([
                    'status' => false,
                    'message' => 'الحساب غير فعال. تم إرسال رمز التحقق لتفعيل الحساب إلى بريدك الإلكتروني.',
                    'is_active' => false,
                    'requires_otp' => true,
                    'data' => [
                        'email' => $user->email,
                        'action' => 'activate_account',
                        'expires_in' => 10
                    ]
                ], 401);
            }

            // محاولة المصادقة
            if (!$token = JWTAuth::attempt($credentials)) {
                // زيادة عداد المحاولات الفاشلة
                $failedAttempts = $this->incrementFailedLoginAttempts($user);

                // إذا وصلت المحاولات الفاشلة إلى 3، إرسال OTP للتحقق
                if ($failedAttempts >= 3) {
                    $otp = $this->generateOtp();
                    $user->update([
                        'otp' => $otp,
                        'otp_expires_at' => Carbon::now()->addMinutes(10)
                    ]);

                    $this->sendOtpEmail($user->email, $otp, 'تأمين الحساب');

                    return response()->json([
                        'status' => false,
                        'message' => 'كلمة المرور غير صحيحة. تم إرسال رمز تحقق إضافي إلى بريدك الإلكتروني.',
                        'requires_otp' => true,
                        'failed_attempts' => $failedAttempts,
                        'data' => [
                            'email' => $user->email,
                            'action' => 'secure_account',
                            'expires_in' => 10,
                        ]
                    ], 401);
                }

                return response()->json([
                    'status' => false,
                    'message' => 'بيانات الاعتماد غير صحيحة',
                    'failed_attempts' => $failedAttempts
                ], 401);
            }

            // إعادة تعيين محاولات تسجيل الدخول الفاشلة
            $this->resetFailedLoginAttempts($user);

            // جلب بيانات المستخدم
            $user = Auth::user();

            return response()->json([
                'status' => true,
                'message' => 'تم تسجيل الدخول بنجاح',
                'data' => [
                    'user' => $user->only(['id', 'name', 'email', 'phone']),
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء تسجيل الدخول',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * تسجيل الخروج
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'status' => true,
                'message' => 'تم تسجيل الخروج بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء تسجيل الخروج',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * إرسال رمز OTP
     */
    public function sendOtp(SendOtpRequest $request)
    {
        try {
            $user = User::withTrashed()->where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'المستخدم غير موجود'
                ], 404);
            }

            // التحقق إذا كان الحساب محذوفاً
            if ($user->trashed()) {
                return response()->json([
                    'status' => false,
                    'message' => 'الحساب محذوف',
                    'is_deleted' => true
                ], 401);
            }

            // إنشاء رمز OTP جديد
            $otp = $this->generateOtp();
            $user->update([
                'otp' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(10)
            ]);

            // إرسال OTP عبر البريد الإلكتروني
            $this->sendOtpEmail($user->email, $otp, 'التحقق العام');

            return response()->json([
                'status' => true,
                'message' => 'تم إرسال رمز التحقق إلى بريدك الإلكتروني',
                'data' => [
                    'email' => $user->email,
                    'expires_in' => 10
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء إرسال رمز التحقق',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * التحقق من رمز OTP
     */
    public function verifyOtp(VerifyOtpRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'المستخدم غير موجود'
                ], 404);
            }

            // التحقق من صلاحية OTP
            if (!$user->otp || $user->otp !== $request->otp) {
                return response()->json([
                    'status' => false,
                    'message' => 'رمز التحقق غير صحيح'
                ], 400);
            }

            // التحقق من انتهاء صلاحية OTP
            if (Carbon::now()->gt($user->otp_expires_at)) {
                return response()->json([
                    'status' => false,
                    'message' => 'انتهت صلاحية رمز التحقق'
                ], 400);
            }

            // مسح رمز OTP بعد التحقق الناجح
            $user->update([
                'otp' => null,
                'otp_expires_at' => null,
                'is_active' => true,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'تم التحقق من رمز OTP بنجاح',
                'data' => [
                    'email' => $user->email,
                    'verified' => true,
                    'is_active'=> true
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء التحقق من رمز OTP',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * إعادة تعيين كلمة المرور
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'المستخدم غير موجود'
                ], 404);
            }

            // التحقق من صحة OTP أولاً
            if (!$user->otp || $user->otp !== $request->otp) {
                return response()->json([
                    'status' => false,
                    'message' => 'رمز التحقق غير صحيح'
                ], 400);
            }

            // التحقق من انتهاء صلاحية OTP
            if (Carbon::now()->gt($user->otp_expires_at)) {
                return response()->json([
                    'status' => false,
                    'message' => 'انتهت صلاحية رمز التحقق'
                ], 400);
            }

            // تحديث كلمة المرور
            $user->update([
                'password' => Hash::make($request->password),
                'otp' => null,
                'otp_expires_at' => null,
                'is_active' => true,

            ]);

            return response()->json([
                'status' => true,
                'message' => 'تم إعادة تعيين كلمة المرور بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء إعادة تعيين كلمة المرور',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * استعادة الحساب المحذوف بعد التحقق من OTP
     */
    public function restoreAccount(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'string', 'email'],
                'otp' => ['required', 'string', 'size:6'],
            ]);

            // البحث عن المستخدم مع الحسابات المحذوفة
            $user = User::withTrashed()->where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'المستخدم غير موجود'
                ], 404);
            }

            // التحقق من صحة OTP
            if (!$user->otp || $user->otp !== $request->otp) {
                return response()->json([
                    'status' => false,
                    'message' => 'رمز التحقق غير صحيح'
                ], 400);
            }

            // التحقق من انتهاء صلاحية OTP
            if (Carbon::now()->gt($user->otp_expires_at)) {
                return response()->json([
                    'status' => false,
                    'message' => 'انتهت صلاحية رمز التحقق'
                ], 400);
            }

            // استعادة الحساب المحذوف
            if ($user->trashed()) {
                $user->restore();

                // تحديث حالة الحساب إلى نشط
                $user->update([
                    'is_active' => true,
                    'otp' => null,
                    'otp_expires_at' => null
                ]);

                // إنشاء توكن للمستخدم بعد الاستعادة
                $token = JWTAuth::fromUser($user);

                return response()->json([
                    'status' => true,
                    'message' => 'تم استعادة الحساب بنجاح',
                    'data' => [
                        'user' => $user->only(['id', 'name', 'email', 'phone']),
                        'access_token' => $token,
                        'token_type' => 'bearer',
                        'expires_in' => config('jwt.ttl') * 60
                    ]
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'الحساب غير محذوف'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء استعادة الحساب',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * تفعيل الحساب غير الفعال بعد التحقق من OTP
     */
    public function activateAccount(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'string', 'email'],
                'otp' => ['required', 'string', 'size:6'],
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'المستخدم غير موجود'
                ], 404);
            }

            // التحقق من صحة OTP
            if (!$user->otp || $user->otp !== $request->otp) {
                return response()->json([
                    'status' => false,
                    'message' => 'رمز التحقق غير صحيح'
                ], 400);
            }

            // التحقق من انتهاء صلاحية OTP
            if (Carbon::now()->gt($user->otp_expires_at)) {
                return response()->json([
                    'status' => false,
                    'message' => 'انتهت صلاحية رمز التحقق'
                ], 400);
            }

            // تفعيل الحساب
            if (!$user->is_active) {
                $user->update([
                    'is_active' => true,
                    'otp' => null,
                    'otp_expires_at' => null
                ]);

                // إنشاء توكن للمستخدم بعد التفعيل
                $token = JWTAuth::fromUser($user);

                return response()->json([
                    'status' => true,
                    'message' => 'تم تفعيل الحساب بنجاح',
                    'data' => [
                        'user' => $user->only(['id', 'name', 'email', 'phone']),
                        'access_token' => $token,
                        'token_type' => 'bearer',
                        'expires_in' => config('jwt.ttl') * 60
                    ]
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'الحساب مفعل بالفعل'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء تفعيل الحساب',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * التحقق من حالة الحساب المحذوف
     */
    public function checkDeletedAccount(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'string', 'email']
            ]);

            $user = User::withTrashed()->where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'المستخدم غير موجود',
                    'is_deleted' => false,
                    'exists' => false
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => $user->trashed() ? 'الحساب محذوف' : 'الحساب نشط',
                'data' => [
                    'is_deleted' => $user->trashed(),
                    'exists' => true,
                    'deleted_at' => $user->deleted_at,
                    'is_active' => $user->is_active,
                    'email' => $user->email
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء التحقق من حالة الحساب',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * التحقق من أمان الحساب بعد عدة محاولات فاشلة
     */
    public function secureAccount(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'string', 'email'],
                'otp' => ['required', 'string', 'size:6'],
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'المستخدم غير موجود'
                ], 404);
            }

            // التحقق من صحة OTP
            if (!$user->otp || $user->otp !== $request->otp) {
                return response()->json([
                    'status' => false,
                    'message' => 'رمز التحقق غير صحيح'
                ], 400);
            }

            // التحقق من انتهاء صلاحية OTP
            if (Carbon::now()->gt($user->otp_expires_at)) {
                return response()->json([
                    'status' => false,
                    'message' => 'انتهت صلاحية رمز التحقق'
                ], 400);
            }

            // مسح رمز OTP
            $user->update([
                'otp' => null,
                'otp_expires_at' => null,
                'is_active' => true,

            ]);

            // إعادة تعيين محاولات تسجيل الدخول الفاشلة
            $this->resetFailedLoginAttempts($user);

            return response()->json([
                'status' => true,
                'message' => 'تم التحقق من أمان الحساب بنجاح',
                'data' => [
                    'email' => $user->email,
                    'verified' => true
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء التحقق من أمان الحساب',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * تحديث التوكن
     */
    public function refresh()
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'status' => true,
                'message' => 'تم تحديث التوكن بنجاح',
                'data' => [
                    'access_token' => $newToken,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء تحديث التوكن',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * معلومات المستخدم الحالي
     */
    public function me()
    {
        try {
            $user = Auth::user();

            return response()->json([
                'status' => true,
                'message' => 'بيانات المستخدم',
                'data' => [
                    'user' => $user->only(['id', 'name', 'email', 'phone', 'created_at', 'is_active'])
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء جلب بيانات المستخدم',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * التحقق من البريد الإلكتروني بعد التسجيل
     */
    public function verifyEmail(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'string', 'email'],
                'otp' => ['required', 'string', 'size:6'],
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'المستخدم غير موجود'
                ], 404);
            }

            // التحقق من صحة OTP
            if (!$user->otp || $user->otp !== $request->otp) {
                return response()->json([
                    'status' => false,
                    'message' => 'رمز التحقق غير صحيح'
                ], 400);
            }

            // التحقق من انتهاء صلاحية OTP
            if (Carbon::now()->gt($user->otp_expires_at)) {
                return response()->json([
                    'status' => false,
                    'message' => 'انتهت صلاحية رمز التحقق'
                ], 400);
            }

            // تأكيد البريد الإلكتروني
            $user->update([
                'email_verified_at' => Carbon::now(),
                'is_active' => true,
                'otp' => null,
                'otp_expires_at' => null
            ]);

            // إنشاء توكن جديد بعد التحقق
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'status' => true,
                'message' => 'تم التحقق من البريد الإلكتروني بنجاح',
                'data' => [
                    'user' => $user->only(['id', 'name', 'email', 'phone', 'email_verified_at']),
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء التحقق من البريد الإلكتروني',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * ========================
     * الدوال المساعدة (Private)
     * ========================
     */

    /**
     * توليد رمز OTP عشوائي
     */
    private function generateLocalEmailFromPhone(string $phone): string
    {
        $normalized = preg_replace('/\D+/', '', $phone);

        if ($normalized === '') {
            $normalized = Str::slug($phone) ?: Str::random(10);
        }

        return $normalized . '@xstore.local';
    }

    private function generateOtp()
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * إرسال رمز OTP عبر البريد الإلكتروني
     */
    private function sendOtpEmail($email, $otp, $action = 'التحقق')
    {
        try {
            Mail::to($email)->send(new OtpMail($otp, $action));
            return true;
        } catch (\Exception $e) {
            Log::error('فشل إرسال OTP: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * زيادة عداد المحاولات الفاشلة
     */
    private function incrementFailedLoginAttempts($user)
    {
        $attempts = cache()->get("failed_login_{$user->id}", 0);
        $attempts++;
        cache()->put("failed_login_{$user->id}", $attempts, now()->addHours(1));

        return $attempts;
    }

    /**
     * إعادة تعيين محاولات تسجيل الدخول الفاشلة
     */
    private function resetFailedLoginAttempts($user)
    {
        cache()->forget("failed_login_{$user->id}");
    }

    /**
     * التحقق من حالة التوكن
     */
    public function checkToken()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if ($user) {
                return response()->json([
                    'status' => true,
                    'message' => 'التوكن صالح',
                    'data' => [
                        'is_valid' => true,
                        'user' => $user->only(['id', 'name', 'email'])
                    ]
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'التوكن غير صالح',
                'data' => [
                    'is_valid' => false
                ]
            ], 401);
        }
    }
}
