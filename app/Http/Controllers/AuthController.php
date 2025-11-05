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
    public function register(Request $request)
    {
        // 1. التحقق من صحة البيانات (Validation)
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users', // تأكد من أنه فريد
            'password' => 'required|string|min:8|confirmed', // 'confirmed' تتطلب حقل password_confirmation
        ]);

        // 2. إنشاء المستخدم وتشفير كلمة المرور
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            // ضروري جداً: تشفير كلمة المرور قبل حفظها
            'password' => Hash::make($request->password), 
        ]);

        // 3. إصدار الـ Token مباشرة (لتسجيل دخول تلقائي بعد التسجيل)
        $token = $user->createToken('authToken')->plainTextToken;

        // 4. إرجاع الاستجابة الناجحة
        return response()->json([
            'token' => $token,
            'user' => $user,
            'message' => '✅ تم إنشاء حسابك بنجاح وتسجيل دخولك تلقائياً.'
        ], 201); // 201 Created هو رمز الاستجابة القياسي لإنشاء مورد جديد
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