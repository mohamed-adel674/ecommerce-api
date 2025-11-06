<?php

namespace App\Models;

// استخدام Traits Laravel الأساسية
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

// استيراد Traits الوظائف الإضافية
use Laravel\Sanctum\HasApiTokens;
use Laravel\Cashier\Billable; // <--- تأكد من استيراد هذا
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    // دمج الـ Traits في الكلاس
    use HasFactory, Notifiable, HasApiTokens, Billable, HasRoles; // <--- تأكد من وجود Billable هنا
    
    /**
     * العلاقة مع سلة التسوق.
     */
    public function cart(): HasOne
    {
        // المستخدم لديه سلة واحدة (واحد لواحد)
        return $this->hasOne(Cart::class);
    }

    /**
     * العلاقة مع الطلبات.
     */
    public function orders(): HasMany
    {
        // المستخدم لديه العديد من الطلبات (واحد لمتعدد)
        return $this->hasMany(Order::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}