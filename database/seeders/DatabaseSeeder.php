<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. إنشاء الأدوار
        // إذا كان الدور غير موجود، يتم إنشاؤه
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'customer']);

        // 2. إنشاء مستخدم مسؤول (أو البحث عنه إذا كان موجوداً)
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'], // الشرط للبحث
            [
                'name' => 'Admin User',
                'password' => Hash::make('12345678'), // كلمة مرور قوية للاختبار
            ]
        );

        // 3. تعيين دور 'admin' للمستخدم
        if (!$adminUser->hasRole('admin')) {
            $adminUser->assignRole('admin');
            echo "Admin user created and assigned role successfully.\n";
        } else {
            echo "Admin user already exists and has the role.\n";
        }
    }
}