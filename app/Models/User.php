<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'user_role',
        'password',
        'is_active'
        
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public static function getFirstUser($condition) {
        return self::where($condition)->first();
    }

    public static function adduser($data) {
        $data = self::Create($data);
        return $data->id;
    }

    public static function checkExists($condition) {
        return self::where($condition)->first();
    }

    public static function getLoggedUserData($loginUsername, $isActive) {
        return self::select('id', 'first_name', 'last_name', 'email', 'phone','user_role','password')
        ->where(function ($query) use ($loginUsername) {
            $query->where('email', $loginUsername)
                ->orWhere('phone', $loginUsername);
        })
        ->where('is_active', $isActive)
        ->first();
    }

    public static function updateUser($id, $data) {
        self::where('id', $id)->update($data);
    }
}
