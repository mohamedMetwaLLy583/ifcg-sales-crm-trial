<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Requests\Api\ChangePasswordRequest;
use App\Http\Requests\Api\UpdateProfileImageRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProfileController extends Controller
{

    public function show(): JsonResponse
    {
        try {
            $user = Auth::user();

            return response()->json([
                'success' => true,
                'message' => 'تم جلب بيانات البروفايل بنجاح',
                'data' => new UserResource($user)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب بيانات البروفايل',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $data = $request->validated();

            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            if ($request->hasFile('profile_image')) {
                if ($user->profile_image) {
                    $oldImagePath = public_path('uploads/' . $user->profile_image);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $file = $request->file('profile_image');
                $fileName = $this->generateFileName($file, $user->id);
                $file->move(public_path('uploads'), $fileName);

                $data['profile_image'] = $fileName;
            }

            $user->update($data);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث البروفايل بنجاح',
                'data' => new UserResource($user->fresh())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث البروفايل',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function updateProfileImage(UpdateProfileImageRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if ($user->profile_image) {
                $oldImagePath = public_path('uploads/' . $user->profile_image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $file = $request->file('profile_image');
            $fileName = $this->generateFileName($file, $user->id);
            $file->move(public_path('uploads'), $fileName);

            $user->update(['profile_image' => $fileName]);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث صورة البروفايل بنجاح',
                'data' => [
                    'profile_image_url' => url('uploads/' . $fileName)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث صورة البروفايل',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    private function generateFileName($file, $userId): string
    {
        $extension = $file->getClientOriginalExtension();
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = Str::slug($originalName);

        return 'user-' . $userId . '-' . time() . '-' . Str::random(5) . '.' . $extension;
    }


    public function destroy(): JsonResponse
    {
        try {
            $user = Auth::user();
            auth()->logout();

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الحساب بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الحساب',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'كلمة المرور الحالية غير صحيحة'
                ], 401);
            }

            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تغيير كلمة المرور بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تغيير كلمة المرور',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
