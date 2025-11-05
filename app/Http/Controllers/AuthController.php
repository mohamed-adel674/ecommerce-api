<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * تسجيل مستخدم جديد (Register).
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users', // البريد يجب أن يكون فريدًا
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // تشفير كلمة المرور
        ]);

        // إنشاء رمز الوصول (Token)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'access_token' => $token, // يجب حفظ هذا الرمز في الواجهة الأمامية
        ], 201);
    }

    /**
     * تسجيل دخول المستخدم (Login).
     */
   public function login(Request $request)
    {
        // 1. التحقق من صحة البيانات (الذي قمت به)
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. محاولة المصادقة (Auth::attempt)
        // إذا فشل التحقق من البريد وكلمة المرور...
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'بيانات الاعتماد (البريد أو كلمة المرور) غير صحيحة.'
            ], 401); // 401 Unauthorized هو رمز الخطأ القياسي للمصادقة الفاشلة
        }

        // 3. إصدار الرمز المميز (Token Generation)
        // إذا نجحت المصادقة، نحصل على كائن المستخدم
        $user = $request->user(); 
        
        // نستخدم دالة createToken من Sanctum لإنشاء Token
        $token = $user->createToken('authToken')->plainTextToken; 

        // 4. إرجاع الـ Token والاستجابة الناجحة
        return response()->json([
            'token' => $token,
            // من الجيد إرجاع بيانات المستخدم الأساسية أيضاً
            'user' => $user, 
            'message' => 'تم تسجيل الدخول بنجاح. استخدم الـ Token هذا لحماية الطلبات.'
        ]);
    }

public function userDetails(Request $request)
    {
        // عند استخدام 'auth:sanctum' middleware، يتم تخزين بيانات المستخدم
        // الذي نجح في المصادقة تلقائياً داخل $request->user().
        
        return response()->json([
            'user' => $request->user()
        ]);
    }
    /**
     * تسجيل خروج المستخدم (Logout).
     */
    public function logout(Request $request): JsonResponse
    {
        // حذف الرمز المستخدم حاليًا (الذي أرسله العميل)
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}