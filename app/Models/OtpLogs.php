<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpLogs extends Model
{
    use HasFactory;

    protected $fillable = [
        'users_id',
        'otp',
        'token',
        'expiry_date'
    ];


    public static function addOtpLogs($data) {
        self::Create($data);
    }

    public static function checkExist($condition) {
        return self::where($condition)->exists();
    }

    public static function validateOtp($condition) {
        return self::select('users_id', 'expiry_date')
             ->where($condition)
             ->first();
    }
}
